import importlib.util
import io
import json
import sys
import tempfile
import unittest
from contextlib import redirect_stderr, redirect_stdout
from datetime import datetime
from pathlib import Path
from types import SimpleNamespace
from unittest.mock import Mock, patch


class ExtractorZktecoTest(unittest.TestCase):
    def setUp(self):
        self.zk = Mock()
        script = Path(__file__).resolve().parents[2] / "scripts/extract_zkteco_attendance.py"
        spec = importlib.util.spec_from_file_location("extractor_zkteco", script)
        self.extractor = importlib.util.module_from_spec(spec)
        with patch.dict(sys.modules, {"zk": SimpleNamespace(ZK=self.zk)}):
            spec.loader.exec_module(self.extractor)

    def ejecutar(self, probe=False, socket_timeout=None, reloj=False):
        with tempfile.TemporaryDirectory() as carpeta:
            salida = Path(carpeta) / "salida.json"
            argumentos = ["extractor", "--ip", "192.0.2.1", "--timeout", "90", "--output", str(salida)]
            if probe:
                argumentos.append("--probe-only")
            if socket_timeout is not None:
                argumentos.extend(["--socket-timeout", str(socket_timeout)])
            if reloj:
                argumentos.extend(["--clock-offset", "-14400"])
            with patch.object(sys, "argv", argumentos), redirect_stdout(io.StringIO()), redirect_stderr(io.StringIO()):
                codigo = self.extractor.main()
            resultado = json.loads(salida.read_text(encoding="utf-8"))
            reloj_path = salida.with_suffix(".clock.json")
            if reloj_path.exists():
                resultado["clock"] = json.loads(reloj_path.read_text(encoding="utf-8"))
            return codigo, resultado

    def test_tcp_sin_respuesta_prueba_udp_con_espera_acotada(self):
        tcp = Mock()
        tcp.connect.side_effect = TimeoutError("TCP sin respuesta")
        udp = Mock()
        conexion = udp.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = []
        self.zk.side_effect = [tcp, udp]

        codigo, resultado = self.ejecutar()

        self.assertEqual(0, codigo)
        self.assertEqual("udp", resultado["transport"])
        self.assertEqual([False, True], [c.kwargs["force_udp"] for c in self.zk.call_args_list])
        self.assertTrue(all(c.kwargs["timeout"] == 30 for c in self.zk.call_args_list))
        conexion.disconnect.assert_called_once()

    def test_lectura_lenta_utiliza_espera_configurada_sin_limitarla_a_diez_segundos(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = []
        codigo, _ = self.ejecutar(socket_timeout=45)
        self.assertEqual(0, codigo)
        self.assertEqual(45, self.zk.call_args.kwargs["timeout"])

    def test_timeout_al_leer_historial_no_se_reporta_como_fallo_de_conexion(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.side_effect = TimeoutError("timed out")
        codigo, resultado = self.ejecutar()
        self.assertEqual(1, codigo)
        self.assertIn("sesion ZKTeco se abrio", resultado["message"])
        self.assertIn("lectura de marcaciones", resultado["message"])
        self.assertEqual(["marcaciones", "marcaciones"], [item["stage"] for item in resultado["attempts"]])
        self.assertEqual(2, conexion.disconnect.call_count)

    def test_sondeo_no_lee_usuarios_ni_marcaciones(self):
        conexion = self.zk.return_value.connect.return_value
        codigo, resultado = self.ejecutar(probe=True)
        self.assertEqual(0, codigo)
        self.assertEqual([], resultado["rows"])
        conexion.get_users.assert_not_called()
        conexion.get_attendance.assert_not_called()
        conexion.disconnect.assert_called_once()
        self.assertEqual(5, self.zk.call_args.kwargs["timeout"])

    def test_fallo_de_ambos_transportes_conserva_sus_errores(self):
        self.zk.return_value.connect.side_effect = TimeoutError("Sin respuesta")
        codigo, resultado = self.ejecutar()
        self.assertEqual(1, codigo)
        self.assertFalse(resultado["ok"])
        self.assertEqual(["tcp", "udp"], [intento["transport"] for intento in resultado["attempts"]])

    def test_sincronizar_tras_reinicio_ajusta_solo_la_hora(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = []
        conexion.get_time.side_effect = [
            datetime(2026, 11, 16, 12, 25), datetime(2026, 11, 16, 12, 25), datetime(2026, 11, 16, 12, 30),
        ]
        referencia = datetime.fromisoformat("2026-11-16T12:30:00-04:00")
        with patch.object(self.extractor, "time", return_value=referencia.timestamp()), patch.object(self.extractor, "sleep"):
            codigo, resultado = self.ejecutar(probe=True, reloj=True)

        self.assertEqual(0, codigo)
        self.assertEqual("adjusted", resultado["clock"]["status"])
        self.assertEqual("2026-11-16T12:30:00", resultado["clock"]["device_time"])
        conexion.set_time.assert_called_once()
        conexion.get_users.assert_not_called()
        conexion.get_attendance.assert_not_called()
        self.zk.return_value.connect.assert_called_once()

    def test_descarga_normal_no_lee_ni_ajusta_la_hora(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = [SimpleNamespace(timestamp=datetime(2026, 11, 16, 8, 30), user_id="1")]
        conexion.get_time.side_effect = TimeoutError("sin respuesta")
        codigo, resultado = self.ejecutar()

        self.assertEqual(0, codigo)
        self.assertEqual(1, len(resultado["rows"]))
        self.assertNotIn("clock", resultado)
        conexion.get_time.assert_not_called()
        conexion.set_time.assert_not_called()
        self.zk.return_value.connect.assert_called_once()

    def test_sincronizar_tras_reinicio_evita_escribir_si_ya_esta_en_hora(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = []
        conexion.get_time.return_value = datetime(2026, 11, 16, 12, 30)
        referencia = datetime.fromisoformat("2026-11-16T12:30:00-04:00")
        with patch.object(self.extractor, "time", return_value=referencia.timestamp()):
            codigo, resultado = self.ejecutar(probe=True, reloj=True)
        self.assertEqual(0, codigo)
        self.assertEqual("in_sync", resultado["clock"]["status"])
        conexion.set_time.assert_not_called()

    def test_revision_programada_escribe_las_marcaciones_antes_de_consultar_la_hora(self):
        conexion = self.zk.return_value.connect.return_value
        conexion.get_users.return_value = []
        conexion.get_attendance.return_value = [SimpleNamespace(timestamp=datetime(2026, 11, 16, 8, 30), user_id="1")]
        def verificar_archivo(conn, offset):
            salida = Path(sys.argv[sys.argv.index("--output") + 1])
            self.assertEqual(1, len(json.loads(salida.read_text(encoding="utf-8"))["rows"]))
            return {"status": "unavailable"}

        with patch.object(self.extractor, "sync_clock", side_effect=verificar_archivo) as ajuste:
            codigo, resultado = self.ejecutar(reloj=True)
        self.assertEqual(0, codigo)
        ajuste.assert_called_once_with(conexion, -14400)
        self.assertEqual({"status": "unavailable"}, resultado["clock"])


if __name__ == "__main__":
    unittest.main()

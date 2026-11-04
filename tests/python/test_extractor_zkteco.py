import importlib.util
import io
import json
import sys
import tempfile
import unittest
from contextlib import redirect_stderr, redirect_stdout
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

    def ejecutar(self, probe=False, socket_timeout=None):
        with tempfile.TemporaryDirectory() as carpeta:
            salida = Path(carpeta) / "salida.json"
            argumentos = ["extractor", "--ip", "192.0.2.1", "--timeout", "90", "--output", str(salida)]
            if probe:
                argumentos.append("--probe-only")
            if socket_timeout is not None:
                argumentos.extend(["--socket-timeout", str(socket_timeout)])
            with patch.object(sys, "argv", argumentos), redirect_stdout(io.StringIO()), redirect_stderr(io.StringIO()):
                codigo = self.extractor.main()
            return codigo, json.loads(salida.read_text(encoding="utf-8"))

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


if __name__ == "__main__":
    unittest.main()

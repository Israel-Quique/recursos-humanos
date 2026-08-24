# Trase Atlas TopoJSON

This repository hosts TopoJSON files for each of the [Trase](https://trase.earth) focus countries.

To align the names of subnational jurisdictions in different countries, three levels have been set:  

* `level1`: country
* `level2`: region/state
* `level3`: municipality/district

## File reference

### Argentina
* Source: [IGN 2019](https://www.ign.gob.ar/NuestrasActividades/InformacionGeoespacial/CapasSIG)
* Projection: http://pacificprojections.spc.int/22182
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/argentina.json)

```js
d3.geoTransverseMercator().rotate([69, 0])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Argentina.png" width="480" height="300">

### Bolivia
* Source: [SIIP WFS Server](http://siip.produccion.gob.bo:8080/geoserver/ows)
* Projection: https://epsg.io/4326
* Download: [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/bolivia.json)


```js
d3.geoTransverseMercator().rotate([50, 55])
```

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Bolivia.png" width="480" height="300">

### Brazil
* Source: [IBGE 2017](https://www.ibge.gov.br/geociencias/organizacao-do-territorio/malhas-territoriais/15774-malhas.html?=&t=downloads)
* Projection: https://epsg.io/5880
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/brazil.json)

```js
d3.geoPolyconic().rotate([54, 0])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Brazil.png" width="480" height="300">

### Colombia
* Source: [DANE 2017](https://geoportal.dane.gov.co/servicios/descarga-y-metadatos/descarga-mgn-marco-geoestadistico-nacional/)
* Projection: https://epsg.io/21899
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/colombia.json)

```js
d3.geoTransverseMercator().rotate([68, 4.6])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Colombia.png" width="480" height="300">


### Côte d'Ivoire
* Source: BNETD
* Projection: https://epsg.io/4326
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/cote_divoire.json)

```js
d3.geoMercator()
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/CotedIvoire.png" width="480" height="300">


### Ecuador
* Source: [INEC 2015](https://www.ecuadorencifras.gob.ec/documentos/web-inec/Cartografia/Clasificador_Geografico/2015/)
* Projection: https://epsg.io/32717
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/ecuador.json)

```js
d3.geoTransverseMercator().rotate([50, 55])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Ecuador.png" width="480" height="300">

### Indonesia
* Source: BIG 2016
* Projection: https://epsg.io/23845
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/indonesia.json)

```js
d3.geoTransverseMercator().rotate([-139.5, 0])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Indonesia.png" width="480" height="300">

### Paraguay
* Source: [DGEEC 2019](http://geo.stp.gov.py/user/dgeec/tables/paraguay_2019_distritos/public)
* Projection: https://epsg.io/32720
* [Download](https://cdn.jsdelivr.net/npm/@trase/trase-atlas@1.1/files/paraguay.json)

```js
d3.geoTransverseMercator().rotate([63, 0])
````

<img src="https://raw.githubusercontent.com/sei-international/trase-atlas/master/img/Paraguay.png" width="480" height="300">

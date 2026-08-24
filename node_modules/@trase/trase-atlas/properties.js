const fs = require("fs");
const d3 = require("d3-dsv");

// load dictionary
const ssv = d3.dsvFormat(";")
const dictionary = ssv.parse(fs.readFileSync("build/dictionary.csv", {encoding: 'utf8'}))

Promise.all([
  parseInput(),
  dictionary,
]).then(output);

function parseInput() {
  return new Promise((resolve, reject) => {
    const chunks = [];
    process.stdin
        .on("data", chunk => chunks.push(chunk))
        .on("end", () => {
          try { resolve(JSON.parse(chunks.join(""))); }
          catch (error) { reject(error); }
        })
        .setEncoding("utf8");
  });
}

function toTitleCase(str) {
  return str.toLowerCase().split(' ').map(function (word) {
    return (word.charAt(0).toUpperCase() + word.slice(1));
  }).join(' ');
}

function output([topology, dictionary]) {
  const keys = dictionary.columns
  level3 = new Map(dictionary.map(d => [d[keys[0]], toTitleCase(d[keys[1]])]));
  level2 = new Map(dictionary.map(d => [d[keys[2]], toTitleCase(d[keys[3]])]));
  for (const geometry of topology.objects.level3.geometries) {
    geometry.properties = {
      name: level3.get(geometry.id)
    };
  }
  for (const geometry of topology.objects.level2.geometries) {
    geometry.properties = {
      name: level2.get(geometry.id)
    };
  }
  process.stdout.write(JSON.stringify(topology));
  process.stdout.write("\n");
}

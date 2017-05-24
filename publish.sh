#! /bin/bash

DIST=${PWD##*/}/dist

# remove old dist and make a new one
rm -rf dist
mkdir dist

cp -r app dist/
cp -r bootstrap dist/
cp -r config dist/
cp -r database dist/
cp -r public dist/
cp -r resources dist/
cp -r routes dist/
cp -r storage dist/
cp -r vendor dist/
cp -r config dist/
cp .env.example dist/.env

zip -r --quiet dist/sphinge-version_no.zip ./

echo "we're done"

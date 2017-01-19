#Shell script para ser executado pelo travis para subir a documentacao para o github pages
#@ref: https://github.com/ApiGen/ApiGen/wiki/Generate-API-to-Github-pages-via-Travis
#Get ApiGen.phar
wget http://www.apigen.org/apigen.phar

#Generate Api
php apigen.phar generate -s src -d ../gh-pages --title 'Documentação laravel-boletos' --template-theme bootstrap

cd ../gh-pages

#Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

#Add branch
git init
git remote add origin https://${GH_TOKEN}@github.com/eduardokum/laravel-boleto.git > /dev/null
git checkout -B gh-pages

#Push generated files
git add .
git commit -m "Documentacao atualizada"
git push origin gh-pages -fq > /dev/null

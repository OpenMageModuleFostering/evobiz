#!/bin/bash

# on s'arrête à la moindre erreur
set -e

# tests en ligne de commande

# Usage sans paramètres mais avec dans le script urls="http://www.maboutique.com/fr/ http://www.maboutique.com/en/ http://www.maboutique.com/es/"
# bash chemin/du/script/exportMulti.sh

# Usage avec paramètres :
# bash chemin/du/script/exportMulti.sh http://www.maboutique.com/fr/ http://www.maboutique.com/en/ http://www.maboutique.com/es/


# utilisation dans une crontab
# http://fr.wikipedia.org/wiki/Crontab
#
#Tous les jours à 23h30
# 30 23 * * * /bin/bash chemin/du/script/exportMulti.sh http://www.maboutique.com/fr/ http://www.maboutique.com/en/ http://www.maboutique.com/es/
# ou
# 30 23 * * * /bin/bash chemin/du/script/exportMulti.sh



# répertoire où seront enrgistrés les flux XML
output_dir=$HOME/evoXML
mkdir -p $output_dir





#urls=http://www.maboutique.com/
#urls="http://www.maboutique.com/fr/ http://www.maboutique.com/en/ http://www.maboutique.com/es/"

# urls="http://presta-v14.jvweb.org/fr/"
urls="http://magento1.jvweb.org/french/ http://magento1.jvweb.org/english/ http://magento1.jvweb.org/german/"

# urls passées en parammettre
[[ -n "$@" ]] && urls="$@"


curl=/usr/bin/curl

# est ce que curl est installé ?
if [ ! -f "$curl" ]
then
	echo "curl isn't installed !"

	exit 1
fi

curl="$curl --insecure "


# Fenêtre de téléchargement : Nonbre de produits par vague
window_size=100


for url in $urls
do
 	filename=$($curl -s "${url}export/?filename")

 	echo $output_dir/$filename

 	# Nombre total de fenêtres à parcourir
 	nb_total=$($curl -s "${url}export/?window=$window_size")

	{
		# Entête XML
		echo -n '<?xml version="1.0" encoding="utf-8"?><products>';

		# Parcours total des fenêtres
		for (( w=0; w <= nb_total; w++ ))
		do
			ww=$((w*window_size))

			$curl -s "${url}export/?limit=$ww,$window_size"
		done

		# Pied Xml
		echo -n '</products>';
	} > $output_dir/$filename

done




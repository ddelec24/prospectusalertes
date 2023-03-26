<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

  /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
    En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
    En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
  */
    ajax::init();

	if (init('action') == 'getCities') { 
		ajax::success(getCities(init('term')));
	}
                      
	if (init('action') == 'getStores') { 
		ajax::success(getStores(init('term')));
	}
                      
    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    /*     * *********Catch exeption*************** */
}
catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}


function getCities($k) {
  $url="https://www.pubeco.fr/elastic/pubeco-completion-fr/_search/template";

  $payload = '{"template":{"file":"pubeco-completion"},"params":{"keywords":"%KEYWORD%","id_country":1,"ll":"47.793800,3.558640"}}';
  $payload = str_replace("%KEYWORD%", $k, $payload);
  
  $curl = "curl -X POST -H 'X-Requested-With: XMLHttpRequest' -H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' -d '$payload' '$url'";
  log::add('prospectusalertes', 'debug', '[AJAX] ' . $curl);
  $exec = shell_exec($curl);

  $results = array();
  $json = json_decode($exec, true);
  if(array_key_exists("hits", $json)) {
          $hits = $json['hits'];
          if($hits['total'] > 0) {
                  foreach($hits["hits"] as $hit) {
                          $cleanName = substr($hit["fields"]["url"][0], 0, -11);
                    	  $humanCity = $hit["fields"]["keywords"][0];
                    	  $region = $hit["fields"]["location"][0];
                    	  if($region != "Aux alentours" && stripos($humanCity, "alentours") === false && $region != "France" ) {
                    	  	$results[] = array(
                            		"humanCity" => $humanCity,
                                  	"city" => $cleanName,
                                  	"region" => $region
                                 	);
                          }
                  }
            log::add('prospectusalertes', 'debug', '[AJAX] rés: ' . json_encode($results));
          } else {
                  log::add('prospectusalertes', 'debug', '[AJAX] Pas de résultats');
          }
  } else {
  	log::add('prospectusalertes', 'debug', '[AJAX] Section Hits non trouvé');
  }
  
  return $results;
}
                      
function getStores($k) {
	log::add("prospectusalertes", "debug", "[AJAX] Recherche store: " . $k);
  
  $list = ["3 Suisses", "Action", "AD", "ADS", "Afibel", "Aldi", "Alice Delice", "Alinéa", "Alpes Bureau", "Ambiance &amp; Styles", "AmériGo", "Animal &amp; Co", "Animalis", "Anne Weyburn", "Argel", "Aroma Zone", "Arrivages", "Art &amp; Fenêtres", "Arthur Bonnet", "AS Adventure", "Atac", "Atelier Gabrielle Seillance", "Atlas For Men", "Atlas", "Atmosphera", "Atol", "Aubert", "Auchan Local", "Auchan Supermarché", "Auchan", "Austral Lagons", "Autobacs", "Autour de Bébé", "Aviva Cuisines", "Axtem", "Babies R Us", "Babou", "Banquiz", "Baobab", "Batiland", "Batiman", "Batkor", "Bazarland", "Be Digital", "Beachcomber Tours", "Becquet", "Bernard Solfin", "Bestdrive", "Bi1", "BigMat", "Bio Monde", "Bio-Planet", "Biocoop", "Blanc Brun", "Blancheporte", "Blanclarence", "Bo Concept", "Boden", "Body Nature", "Bofrost", "Bon Prix", "Botanic", "Boucherie Aurélien", "Boulanger", "Bouygues Telecom", "Brake", "Brico Cash", "Brico Dépôt", "Brico Pro", "Bricolex", "Bricoman", "Bricomarché", "Bricorama", "Bruno Flaujac", "Bureau Vallée", "Buro+", "But", "Buttinette", "Bébé9", "C&amp;A", "Cabesto", "Calipage", "CapAnimal", "Carrefour City", "Carrefour Contact", "Carrefour Express", "Carrefour Market", "Carrefour Supeco", "Carrefour", "Carré Blanc", "Carter Cash", "Casa", "Cash Piscines", "CashVin", "Casino Shop", "Casino", "Casti Prix", "Castorama", "Caséo", "Cedeo", "Centrakor", "Chabert Duval", "Champion", "Chauss Expo", "Chaussea", "Chausson Matériaux", "Chien Chat et Compagnie", "Christine Laure", "Chronodrive", "Chrétien", "Cinna", "Cleor", "Club Med", "Cléau", "Coccimarket", "Coccinelle Express", "Coccinelle Supermarché", "Cocktail Scandinave", "Coiff&amp;Co", "Colruyt", "Compétence", "Comtesse du Barry", "Concept Alu", "Conforama", "Confort &amp; Vie", "Connexion", "CookCooning", "Coop Atlantique", "Copra", "Cora", "Coriolis Telecom", "Corolle", "Corsica Tours", "Costco", "Crack", "Croquegel", "Crozatier", "Cuir Center", "Cuisine Plaisir", "Cuisine Plus", "Cuisinella", "Cuisines Schmidt", "Culinarion", "Cultura", "Culture Vélo", "Cyrillus", "Célio", "CôtéHalles", "Dalbe", "Damart", "Daniel Moquet", "Darty", "Davigel", "Daxon", "Decathlon", "Decitre", "Delbard", "Delko", "Denis Matériaux", "Desamais Findis", "Descamps", "Desjoyaux", "Diagonal", "Disney Store", "Districenter", "Domial", "Domigel", "Donjon", "Dr. Pierre Ricaud", "Dreamland", "Drim", "Dya Shopping", "Décor Discount", "Ecomiam", "Eismann", "Electrodépôt", "Envie de Salle de Bain", "Episaveurs", "Eric Bompard", "Espace Emeraude", "Espace Terrena", "Essem'Bio", "Essilor", "Eur-Auto", "Eureka", "Eurekakids", "Euromaster", "Eurotyre", "Expert", "Extra", "Fabrique de Styles", "Fauchon", "Feu Vert", "Firststop", "Fleurance Nature", "Flunch", "Flying Tiger", "Fnac Kids", "Fnac", "Forum+", "Fragonard", "Frais D'Ici", "France Literie", "France Loisirs", "Franck Provost", "Franprix", "Françoise Saget", "FTI Voyages", "Furet du Nord", "G20", "Galeries Lafayette", "Gallery Tendances", "Gamm Vert", "Gautier", "Gedibois", "Gedimat", "Gel2000", "Gellik", "Geox", "Gifi", "Gitem", "Go Sport", "Grand Frais", "Grandoptical", "Gustave Rideau", "Guy Demarle", "Géant Casino", "Gémo", "Générale d'Optique", "H&amp;H", "Habitat", "Hediard", "Helline", "Hema", "Hespéride", "Home Salons", "Hygena", "Hyper U", "Hyperburo", "Hôtels Circuits France", "Ibureau", "Idealsko", "Idées Homme", "ikea", "Imaginéa", "Interior's", "Intermarché", "Intersport", "IoBuro", "IrriJardin", "Isère Bureau", "Ivantout", "Ixina", "Jacques Briant", "Jardiland", "Jardin et Saisons", "Jardival", "Jeff de Bruges", "Jour de Fête", "Jours Heureux", "Joué Club", "Jysk", "Kandy", "Keria Luminaires", "Kiabi", "King Jouet", "Kiriel", "Kruidvat", "Krys", "L'Ameublier", "L'Atelier de Lucie", "L'Eau Vive", "L'Incroyable", "L'Orange Bleue", "La Boîte à Pizza", "La Chaise Longue", "La Compagnie du Lit", "La Foir'Fouille", "La Grande Récré", "La Halle Au Sommeil", "La Halle", "La Maison du Jersey", "La Maison.fr", "La Meublerie", "La Poste Mobile", "La Redoute", "La Vie Claire", "La Vignery", "Lagrange", "Lands' End", "Lapeyre", "Laura Kent", "Laurie Lumière", "Le Bon Marché", "Le Bonhomme De Bois", "Le Grand Panier Bio", "Le Géant des Beaux-Arts", "Le Géant du Meuble", "Le Marchand Bio", "Le Roi Du Matelas", "Leader Price", "Leclerc Local", "Leclerc", "Lego", "Leroy Merlin", "Les Briconautes", "Les Compagnons des Saisons", "Les Comptoirs de la Bio", "Les Experts Meubles", "Les Halles d'Auchan", "Les Opticiens Conseils", "Lidl", "Lifetime Kidsroom", "Ligne Roset", "Linvosges", "Litrimarché", "Loberon", "LR World", "Lynx Optique", "Lyreco", "Lysadis", "M6 Boutique", "Madeleine", "Maga", "Magasin Vert", "Magellan", "Maison du Frais", "Maison Dépôt", "Maison et Confort", "Maison Ricot", "Maison XXL", "Maison à Vivre", "Maisons Du Monde", "Majuscule", "Makita", "Marché aux Affaires", "Mario Bertulli", "Marionnaud", "Master Mat", "Master Pro", "Match", "Mathon", "Maty", "Maxi Bazar", "Maxi Toys", "Maxi Zoo", "Maximarché", "Maximo", "Maxxess", "Mazélie &amp; Co", "Mc Donald's", "MDA", "Meilland Richardier", "Meublena", "Meubles du Mené", "Micromania", "Midas", "Migros", "Mister Menuiserie", "Mobalpa", "Mobilier De France", "Mon Brico", "Mon Vert Jardin", "Monoprix", "Monsieur Meuble", "Moulin Roty", "Mr Bricolage", "Mr Jardinage", "My Auchan", "Métro", "Natalys", "Naturalia", "Nature &amp; Découvertes", "Naturéo", "Netto", "Nicolas", "Nocibé", "Norauto", "Nordiska", "Norma", "Nov'Mod", "Nutrimetics", "O Marché Frais", "OBI", "Office Dépôt", "Olivier Desforges", "Optic 2000", "Optical Center", "Optical Discount", "Orange", "Orchestra", "Outiror", "Oxybul", "Pacific Pêche", "Panier Sympa", "Partylite", "Peggy Sage", "Peter Hahn", "Petit Casino", "PharmaBest", "Pharmacie Lafayette", "Pharmavie", "Pia", "Picard", "PicwicToys", "Pizza Hut", "Pièces Auto", "Place de la Literie", "Place du Marché", "Playmobil", "Plein Ciel", "Plus Belle l'Europe", "Point P", "Point S", "Point Vert", "Printemps", "Pro &amp; Cie", "Profil+", "Proloisirs", "Promocash", "Provenc'Halles", "Provence Outillage", "Proxi Confort", "Pulsat", "Pôle Vert", "Quick", "RAGT", "Retif", "Roady", "Rochebobois", "Rouge Papier", "Rue du Commerce", "Rural Master", "Rénoval", "Saint Maclou", "Sainthimat", "Sajou", "Satoriz", "Sauthon", "Saveurs d'Orient", "Scar", "Schleich", "Secrets de Voyages", "Seguin", "Sephora", "SFR", "Shopix", "Sitis", "Sneakers Specialist", "So Coo'c", "Solignac", "Sostrene Grene", "Spar", "Sport 2000", "Stanhome", "Starjouet", "Stihl", "Stokomani", "Sud-Ouest Aliment", "Super U", "Sysco", "Sédao", "Takko", "Tati", "Temps L", "Texam", "Thiriet", "Thés de la Pagode", "Tom &amp; Co", "ToolStation", "Top Office", "Toupargel", "Tout Faire Matériaux", "Toys R Us", "Trafic", "Tridome", "Truffaut", "TUI", "Tupperware", "Télé Shopping", "U Express", "Un Jour Ailleurs", "Une Heure Pour Soi", "Union Matériaux", "Uniqlo", "Utile", "Vertbaudet", "Verts Loisirs", "Victoria", "Villa Verde", "Vision Plus", "Vitrine Magique", "Vival", "VM Matériaux", "Vulco", "VVF Villages", "Végétalis", "Véranda Rideau", "Weber", "Weldom", "Willemse", "Xooon", "Yves Rocher", "Zeeman", "Zodio", "Zoe Confetti", "Zooplus"];

  $results = array();

  foreach ($list as $e) {
    if (strpos(strtolower($e), strtolower($k)) !== false) { $results[] = htmlspecialchars_decode($e); }
  }
  
  return $results;


}
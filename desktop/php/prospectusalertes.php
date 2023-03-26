<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('prospectusalertes');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes Alertes}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucune alerte trouvée, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			// Champ de recherche
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
            	if(!$eqLogic->getConfiguration('type')) {
                  $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                  echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                  echo '<img src="' . $eqLogic->getImage() . '"/>';
                  echo '<br>';
                  echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                  echo '<span class="hiddenAsCard displayTableRight hidden">';
                  echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
                  echo '</span>';
                  echo '</div>';
                }
			}
			echo '</div>';
		}
		?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs">  {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux et spécifiques de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'alerte}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'alerte}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" >{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Localisation}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Commune *}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Renseignez ici votre commune pour avoir les prospectus ciblés}}"></i></sup>
								</label>
								<div class="col-sm-6">
                                	<input type="hidden" class="eqLogicAttr" data-l1key="configuration" data-l2key="city" >
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="humanCity" placeholder="{{Saisissez le début de votre commune et choisissez dans la liste}}">
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Liste des enseignes à surveiller}}</legend>
							<div class="form-group">
							<label class="col-sm-4 control-label">{{Enseignes *}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Choisissez la ou les enseignes qui vous intéressent}}"></i></sup>
								</label>
								<div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control flexdatalist" data-l1key="configuration" data-l2key="stores" />
								</div>
								<label class="col-sm-4 control-label"></label>
                                <div class="col-sm-6">
                                	Saisissez une partie du nom de l'enseigne et cliquez sur le résultat souhaité pour valider votre choix.
                                </div>
							</div>
                            
                            <hr/>
                        	<!-- Exemple de champ de saisie du cron d'auto-actualisation avec assistant -->
							<!-- La fonction cron de la classe du plugin doit contenir le code prévu pour que ce champ soit fonctionnel -->
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Auto-actualisation}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de rafraîchissement des commandes infos de l'équipement}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<div class="input-group">
										<input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Cliquer sur ? pour afficher l'assistant cron}}">
										<span class="input-group-btn">
											<a class="btn btn-default cursor jeeHelper roundedRight" data-helper="cron" title="Assistant cron">
												<i class="fas fa-question-circle"></i>
											</a>
										</span>
									</div>
								</div>
							</div>
                            
						</div>
                        
						<!-- Partie droite de l onglet Équipement -->
						<div class="col-lg-6">
                            <div class="input-group pull-right" style="display:inline-flex; padding-top: 15px">
                                <span class="input-group-btn"></span>
                                <a class="btn btn-xs btn-info bt_addAction roundedLeft" data-type="newcmd"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
                            </div>
							<legend><i class="fas fa-info"></i> {{Envoi des notifications}}</legend>
							<div id="actiontab" class="form-group">
								<div id="div_newcmd"></div>
							</div>
						</div>

					</fieldset>
				</form>
			</div><!-- /.tabpanel #eqlogictab-->

				<!-- Onglet des commandes de l'équipement -->
				<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
				<br><br>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
								<th style="min-width:200px;width:350px;">{{Nom}}</th>
								<th>{{Type}}</th>
								<th style="min-width:260px;">{{Options}}</th>
								<th>{{Etat}}</th>
								<th style="min-width:80px;width:200px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
                
			</div><!-- /.tabpanel #commandtab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<style>
.flexdatalist-results{position:absolute;top:0;left:0;border:1px solid #444;border-top:none;background:#fff;z-index:100000;max-height:300px;overflow-y:auto;box-shadow:0 4px 5px rgba(0,0,0,0.15);color:#333;list-style:none;margin:0;padding:0}.flexdatalist-results li{border-bottom:1px solid #ccc;padding:8px 15px;font-size:14px;line-height:20px}.flexdatalist-results li span.highlight{font-weight:700;text-decoration:underline}.flexdatalist-results li.active{background:#2B82C9;color:#fff;cursor:pointer}.flexdatalist-results li.no-results{font-style:italic;color:#888}.flexdatalist-results li.group{background:#F3F3F4;color:#666;padding:8px 8px}.flexdatalist-results li .group-name{font-weight:700}.flexdatalist-results li .group-item-count{font-size:85%;color:#777;display:inline-block;padding-left:10px}.flexdatalist-multiple:before{content:'';display:block;clear:both}.flexdatalist-multiple{width:100%;margin:0;padding:0;list-style:none;text-align:left;cursor:text}.flexdatalist-multiple.disabled{background-color:#eee;cursor:default}.flexdatalist-multiple:after{content:'';display:block;clear:both}.flexdatalist-multiple li{display:inline-block;position:relative;margin:5px}.flexdatalist-multiple li.input-container,.flexdatalist-multiple li.input-container input{border:none;height:auto;padding:0 0 0 4px;line-height:24px}.flexdatalist-multiple li.value{display:inline-block;padding:2px 25px 2px 7px;background:#efefef;border-radius:3px;color:#444;line-height:20px;float:left}.flexdatalist-multiple li.toggle{cursor:pointer;transition:opacity ease-in-out 300ms}.flexdatalist-multiple li.toggle.disabled{text-decoration:line-through;opacity:0.80}.flexdatalist-multiple li.value span.fdl-remove{font-weight:700;padding:2px 5px;font-size:20px;line-height:20px;cursor:pointer;position:absolute;top:0;right:0;opacity:0.70}.flexdatalist-multiple li.value span.fdl-remove:hover{opacity:1}
</style>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'flexdatalist', 'js', 'prospectusalertes');?>
<?php include_file('desktop', 'prospectusalertes', 'js', 'prospectusalertes');?>

<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
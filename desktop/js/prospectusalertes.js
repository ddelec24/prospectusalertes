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

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
	axis: "y",
	cursor: "move",
	items: ".cmd",
	placeholder: "ui-state-highlight",
	tolerance: "intersect",
	forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = {configuration: {}}
	}
	if (!isset(_cmd.configuration)) {
		_cmd.configuration = {}
	}
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
	tr += '<td class="hidden-xs">'
	tr += '<span class="cmdAttr" data-l1key="id"></span>'
	tr += '</td>'
	tr += '<td>'
	tr += '<div class="input-group">'
	tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
	tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
	tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
	tr += '</div>'
	tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
	tr += '<option value="">{{Aucune}}</option>'
	tr += '</select>'
	tr += '</td>'
	tr += '<td>'
	tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
	tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
	tr += '</td>'
	tr += '<td>'
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
	tr += '<div style="margin-top:7px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '</div>'
	tr += '</td>'
	tr += '<td>';
	tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'; 
	tr += '</td>';
	tr += '<td>'
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
	tr += '</tr>'
	$('#table_cmd tbody').append(tr)
	var tr = $('#table_cmd tbody tr').last()
	jeedom.eqLogic.buildSelectCmd({
		id:  $('.eqLogicAttr[data-l1key=id]').value(),
		filter: {type: 'info'},
		error: function (error) {
			$('#div_alert').showAlert({message: error.message, level: 'danger'})
		},
		success: function (result) {
			tr.find('.cmdAttr[data-l1key=value]').append(result)
			tr.setValues(_cmd, '.cmdAttr')
			jeedom.cmd.changeType(tr, init(_cmd.subType))
		}
	})
}


$( function() {
  
  	// load and display notifications cmd on the right side
  	//var currentId = $( ".eqLogicAttr[data-l1key=id]" ).val();
  	/*var currentId = init('eqLogicId');
  	console.log("currentId = " + currentId);
  	jeedom.eqLogic.byId({
      id : currentId,
      error: function(error) {
        $.fn.showAlert({
          message: error.message,
          level: 'danger'
        })
      },
      success: function(data) {
        console.log(data);
      }
    
    });*/
  
	function split( val ) {
	  return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}
	
	$( ".eqLogicAttr[data-l1key=configuration][data-l2key=stores]" )
	// don't navigate away from the field on tab when selecting an item
	.on( "keydown", function( event ) {
		if ( event.keyCode === $.ui.keyCode.TAB &&
			$( this ).autocomplete( "instance" ).menu.active ) {
			event.preventDefault();
	}
})
	.autocomplete({
      	clearButton: true,
		source: function( request, response ) {
			$.post( "plugins/prospectusalertes/core/ajax/prospectusalertes.ajax.php", {
				action: 'getStores',
				term: extractLast( request.term )
			},
			function(e) {
				if(e.state != "ok")
					console.log("Alertes Prospectus : Erreur lors du traitement");
				response(e.result);
			}
			);
		},
		search: function() {
		  	// custom minLength
			var term = extractLast( this.value );
			if ( term.length < 2 ) {
				return false;
			}
		},
		focus: function() {
		  	// prevent value inserted on focus
			return false;
		},
		select: function( event, ui ) {
			var terms = split( this.value );
		  	// remove the current input
			terms.pop();
		  	// add the selected item
			terms.push( ui.item.value );
		  	// add placeholder to get the comma-and-space at the end
			terms.push( "" );
			this.value = terms.join( ", " );
			return false;
		}
	});
	
  	$( ".eqLogicAttr[data-l1key=configuration][data-l2key=humanCity]" ).on("click", function () {
   		$(this).select();
	});
  
	$( ".eqLogicAttr[data-l1key=configuration][data-l2key=humanCity]" ).autocomplete({
		source: function( request, response ) {
			$.post( "plugins/prospectusalertes/core/ajax/prospectusalertes.ajax.php", {
				action: 'getCities',
				term: extractLast( request.term )
			},
			function(e) {
				if(e.state != "ok")
					console.log("Alertes Prospectus : Erreur lors du traitement");
				response(e.result);
			}
			);
		},
		minLength: 1,
		select: function( event, ui ) {
          	console.log(ui.item.humanCity + " (" + ui.item.region + ")");
			$( ".eqLogicAttr[data-l1key=configuration][data-l2key=humanCity]" ).val(ui.item.humanCity + " (" + ui.item.region + ")");
			$( ".eqLogicAttr[data-l1key=configuration][data-l2key=city]" ).val(ui.item.city);
          	return false;
		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( "<div>" + item.humanCity + " (<em>" + item.region + "</em>)</div>" )
		.appendTo( ul );
	};

  	
});


$('.bt_addAction').off('click').on('click',function(){
	addAction({}, $(this).attr('data-type'));
});

function addAction(_action, _type) {
	if (!isset(_action)) {
		_action = {};
	}
	if (!isset(_action.options)) {
		_action.options = {};
	}
	var div = '<div class="' + _type + ' row" style="margin-bottom:5px">';
  	div += '<div class="col-sm-1"></div>';
	div += '<div class="col-sm-8">';
	div += '<div class="input-group">';
	div += '<span class="input-group-btn">';
	div += '<a class="btn btn-default btn-sm bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
	div += '</span>';
	div += '<input class="eqLogicAttr form-control input-sm cmdAction expressionAttr" data-l1key="configuration" data-l2key="cmdNotif" data-type="' + _type + '" />';
	div += '<span class="input-group-btn">';
	div += '<a class="btn btn-default btn-sm listCmdAction roundedRight" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
	div += '</span>';
	div += '</div>';
	div += '</div>';
	//div += '<div class="col-sm-7 actionOptions">';
	//div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
	//div += '</div>';
	$('#div_' + _type).append(div);
	$('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
	taAutosize();
}

$("body").off('click',".listCmdAction").on('click',".listCmdAction", function () {
	var type = $(this).attr('data-type');
	var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=configuration]');
	jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('.' + type).find('.actionOptions').html(html);
			taAutosize();
		});
	});
});

$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',  function () {
	var type = $(this).attr('data-type');
	$(this).closest('.' + type).remove();
});


function printEqLogic(_eqLogic) {
  	var _type = "newcmd";
    var eqLogicId = _eqLogic.id;
  	var eqLogicCmdNotif = _eqLogic.configuration.cmdNotif;
  	var typeEqLogic = _eqLogic.configuration.type;
  
  	if(!eqLogicCmdNotif)
      return;
  	$("#div_" + _type).empty();
  	if(typeof eqLogicCmdNotif === "string") {
      addAction({}, _type);
      $(".eqLogicAttr[data-l1key=configuration][data-l2key=cmdNotif]").last().val(eqLogicCmdNotif);
      console.log(eqLogicCmdNotif);
      
    } else if (typeof eqLogicCmdNotif === "object") {
      $(eqLogicCmdNotif).each(function(i) { 
      	addAction({}, _type);
        $(".eqLogicAttr[data-l1key=configuration][data-l2key=cmdNotif]").last().val(eqLogicCmdNotif[i]);
        
      });
    }
  
}
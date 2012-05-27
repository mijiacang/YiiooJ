<!--
 Nested Set Admin GUI
 Main View File  index.php

 @author Spiros Kabasakalis <kabasakalis@gmail.com>,myspace.com/spiroskabasakalis
 @copyright Copyright &copy; 2011 Spiros Kabasakalis
 @since 1.0
 @license The MIT License-->

目前大部分内容待添加
<br />
<?php
$course_url= ($model?("/".$model->id):"");
if((UUserIdentity::isTeacher()) ||UUserIdentity::isAdmin())
{
	?>

<ul>
	<!--     <li>If tree is empty,start by creating one or more root nodes.</li> -->
	<li>鼠标右键点击树有各种操作，树的节点可以拖到其他节点下面.</li>
</ul>
<div>
	<div style="float: left">
		<input id="reload" type="button" style="display: block; clear: both;"
			value="Refresh" class="client-val-form button">
	</div>
	<div style="float: left">
		<input id="add_root" type="button"
			style="display: block; clear: both;" value="Create Root"
			class="client-val-form button">
	</div>
</div>
<?php
}
?>

<!--The tree will be rendered in this div-->

<table>
	<tr>
		<td width="20%"><div>
				<table>
					<tr>
						<td>
							<div id="<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>"></div>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td>
			<div id="showchapter"></div>
		</td>
	</tr>
</table>
<script type="text/javascript">
$(function () {
$("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>")
		.jstree({
                           "html_data" : {
	            "ajax" : {
                                 "type":"POST",
 	                          "url" : "<?php echo $baseUrl;?>/chapter/fetchTree<?php echo $course_url ?>",
	                         "data" : function (n) {
	                          return {
                                                  id : n.attr ? n.attr("id") : 0,
                                                  "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                                   };
	                }
  	            }
	        },

"contextmenu":  { 'items': {

"rename" : {
	            "label" : "Rename",
                    "action" : function (obj) { this.rename(obj); }
                  },
"update" : {
	              "label"	: "Update",
	              "action"	: function (obj) {
                                                id=obj.attr("id").replace("node_","");
                     $.ajax({
                                 type: "POST",
                                 url: "<?php echo $baseUrl;?>/chapter/returnForm<?php echo $course_url ?>",
                                data:{
                                          'update_id':  id,
                                           "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                              },
			       'beforeSend' : function(){
                                           $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                               'complete' : function(){
                                           $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                   },
                    success: function(data){

                        $.fancybox(data,
                        {    "transitionIn"	:	"elastic",
                            "transitionOut"    :      "elastic",
                             "speedIn"		:	600,
                            "speedOut"		:	200,
                            "overlayShow"	:	false,
                            "hideOnChapterClick": false,
                             "onClosed":    function(){
                                                                       } //onclosed function
                        })//fancybox

                    } //success
                });//ajax

                                                  }//action function

},//update

    "properties" : {
	"label"	: "Properties",
	"action" : function (obj) {
                                   id=obj.attr("id").replace("node_","")
                             $.ajax({
                                   type:"POST",
			           url:"<?php echo $baseUrl;?>/chapter/returnView",
		                   data:   {
                                             "id" :id,
                                            "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                              },
			         beforeSend : function(){
                                               $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                               },
                                complete : function(){
                                              $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
                               success :  function(data){
                        $.fancybox(data,
                        {    "transitionIn"	:	"elastic",
                            "transitionOut"    :      "elastic",
                             "speedIn"		:	600,
                            "speedOut"		:	200,
                            "overlayShow"	:	false,
                            "hideOnChapterClick": false,
                             "onClosed":    function(){
                                                                       } //onclosed function
                        })//fancybox

                    } //function



		});//ajax

                                                },
	"_class"			: "class",	// class is applied to the item LI node
	"separator_before"	: false,	// Insert a separator before the item
	"separator_after"	: true	// Insert a separator after the item

	},//properties

"remove" : {
	               "label"	: "Delete",
	              "action" : function (obj) {
		       $('<div title="Delete Confirmation">\n\
                     <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>\n\
                    Chapter <span style="color:#FF73B4;font-weight:bold;">'+(obj).attr('rel')+'</span> and all it\'s subcategories will be deleted.Are you sure?</div>')
                       .dialog({
			resizable: false,
			height:170,
			modal: true,
			buttons: {
				       "Delete": function() {
                                        jQuery("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").jstree("remove",obj);
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});

                                                                                     }
},//remove
"create" : {
	"label"	: "Create",
	"action" : function (obj) { this.create(obj); },
        "separator_after": false
	},



                  }//items
                  },//context menu

			// the `plugins` array allows you to configure the active plugins on this instance
			"plugins" : ["themes","html_data","contextmenu","crrm","dnd","ui"],
			// each plugin you have included can have its own config object
			"core" : { "initially_open" : [ <?php echo $open_nodes?> ],'open_parents':true}
			// it makes sense to configure a plugin only if overriding the defaults

		})
		.bind("select_node.jstree", function (event, data) {
           // `data.rslt.obj` is the jquery extended node that was clicked
				id=data.rslt.obj.attr("id").replace("node_","");
				$("#showchapter").load("<?php echo $baseUrl;?>/chapter/returnChapter/"+id);
	        })

                ///EVENTS
               .bind("rename.jstree", function (e, data) {
		$.ajax({
                           type:"POST",
			   url:"<?php echo $baseUrl;?>/chapter/rename",
			   data:  {
				        "id" : data.rslt.obj.attr("id").replace("node_",""),
                                         "new_name" : data.rslt.new_name,
			                 "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                       },
                         beforeSend : function(){
                                                     $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                         complete : function(){
                                                       $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
			success:function (r) {  response= $.parseJSON(r);
				           if(!response.success) {
					                                   $.jstree.rollback(data.rlbk);
				                                            }else{
                                                                               data.rslt.obj.attr("rel",data.rslt.new_name);
                                                                            };
			                   }
		});
	})

         .bind("remove.jstree", function (e, data) {
		$.ajax({
                           type:"POST",
			    url:"<?php echo $baseUrl;?>/chapter/remove",
			    data:{
				        "id" : data.rslt.obj.attr("id").replace("node_",""),
			                "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                        },
                           beforeSend : function(){
                                                     $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                          complete: function(){
                                                       $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
			  success:function (r) {  response= $.parseJSON(r);
				           if(!response.success) {
					                                   $.jstree.rollback(data.rlbk);
				                                            };
			                   }
		});
	})

        .bind("create.jstree", function (e, data) {
                           newname=data.rslt.name;
                           parent_id=data.rslt.parent.attr("id").replace("node_","");
            $.ajax({
                    type: "POST",
                    url: "<?php echo $baseUrl;?>/chapter/returnForm",
                      data:{   'name': newname,
                                 'parent_id':   parent_id,
                                 "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                                          },
                           beforeSend : function(){
                                                     $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                           complete : function(){
                                                       $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
                          success: function(data){

                        $.fancybox(data,
                        {    "transitionIn"	:	"elastic",
                            "transitionOut"    :      "elastic",
                             "speedIn"		:	600,
                            "speedOut"		:	200,
                            "overlayShow"	:	false,
                            "hideOnChapterClick": false,
                             "onClosed":    function(){
                                                                       } //onclosed function
                        })//fancybox

                    } //success
                });//ajax

	})
.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {

                //jstree provides a whole  bunch of properties for the move_node event
                //not all are needed for this view,but they are there if you need them.
                //Commented out logs  are for debugging and exploration of jstree.

                 next= jQuery.jstree._reference('#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>')._get_next (this, true);
                 previous= jQuery.jstree._reference('#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>')._get_prev(this,true);

                    pos=data.rslt.cp;
                    moved_node=$(this).attr('id').replace("node_","");
                    next_node=next!=false?$(next).attr('id').replace("node_",""):false;
                    previous_node= previous!=false?$(previous).attr('id').replace("node_",""):false;
                    new_parent=$(data.rslt.np).attr('id').replace("node_","");
                    old_parent=$(data.rslt.op).attr('id').replace("node_","");
                    ref_node=$(data.rslt.r).attr('id').replace("node_","");
                    ot=data.rslt.ot;
                    rt=data.rslt.rt;
                    copy= typeof data.rslt.cy!='undefined'?data.rslt.cy:false;
                   copied_node= (typeof $(data.rslt.oc).attr('id') !='undefined')? $(data.rslt.oc).attr('id').replace("node_",""):'UNDEFINED';
                   new_parent_root=data.rslt.cr!=-1?$(data.rslt.cr).attr('id').replace("node_",""):'root';
                   replaced_node= (typeof $(data.rslt.or).attr('id') !='undefined')? $(data.rslt.or).attr('id').replace("node_",""):'UNDEFINED';
					if(new_parent_root=='root'){
						alert("The tree allows one root!");
						$.jstree.rollback(data.rlbk);						
						return false;
					}

//                      console.log(data.rslt);
//                      console.log(pos,'POS');
//                      console.log(previous_node,'PREVIOUS NODE');
//                      console.log(moved_node,'MOVED_NODE');
//                      console.log(next_node,'NEXT_NODE');
//                      console.log(new_parent,'NEW PARENT');
//                      console.log(old_parent,'OLD PARENT');
//                      console.log(ref_node,'REFERENCE NODE');
//                      console.log(ot,'ORIGINAL TREE');
//                      console.log(rt,'REFERENCE TREE');
//                      console.log(copy,'IS IT A COPY');
//                      console.log( copied_node,'COPIED NODE');
//                      console.log( new_parent_root,'NEW PARENT INCLUDING ROOT');
//                      console.log(replaced_node,'REPLACED NODE');


			$.ajax({
				async : false,
				type: 'POST',
				url: "<?php echo $baseUrl;?>/chapter/moveCopy",

				data : {
					"moved_node" : moved_node,
                                        "new_parent":new_parent,
                                        "new_parent_root":new_parent_root,
                                         "old_parent":old_parent,
                                         "pos" : pos,
                                         "previous_node":previous_node,
                                          "next_node":next_node,
                                          "copy" : copy,
                                          "copied_node":copied_node,
                                          "replaced_node":replaced_node,
				         "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                                          },
                           beforeSend : function(){
                                                     $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                          complete : function(){
                                                       $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
				success : function (r) {
                                    response=$.parseJSON(r);
					if(!response.success) {
						$.jstree.rollback(data.rlbk);
                                                 alert(response.message);
					}
					else {
                                          //if it's a copy
                                          if  (data.rslt.cy){
						$(data.rslt.oc).attr("id", "node_" + response.id);                         
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
                                          }
                                                                             //  console.log('OK');
					}

				}
			}); //ajax



		});//each function
	});   //bind move event

                ;//JSTREE FINALLY ENDS (PHEW!)

//BINDING EVENTS FOR THE ADD ROOT AND REFRESH BUTTONS.
   $("#add_root").click(function () {
	$.ajax({
                      type: 'POST',
	              url:"<?php echo $baseUrl;?>/chapter/returnForm",
		     data:	{
				    "create_root" : true,
			             "YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"
                                                          },
                                     beforeSend : function(){
                                                     $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").addClass("ajax-sending");
                                                             },
                                     complete : function(){
                                                       $("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").removeClass("ajax-sending");
                                                             },
                                     success:    function(data){

                        $.fancybox(data,
                        {    "transitionIn"	:	"elastic",
                            "transitionOut"    :      "elastic",
                             "speedIn"		:	600,
                            "speedOut"		:	200,
                            "overlayShow"	:	false,
                            "hideOnChapterClick": false,
                             "onClosed":    function(){
                                                                       } //onclosed function
                        })//fancybox

                    } //function

		});//post
	});//click function
	
$("#showchapter").load("<?php echo $baseUrl;?>/chapter/returnChapter/1");
              $("#reload").click(function () {
		jQuery("#<?php echo Chapter::ADMIN_TREE_CONTAINER_ID;?>").jstree("refresh");
	});
});

</script>

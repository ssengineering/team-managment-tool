<?php
/*
  Include these files for functionality
*/
require('../includes/includeme.php');
include('agentLogins.php');

/*
  Collect information about the current user
*/
$auth = -1;

if(can("read", "4f0ece0c-8e55-45aa-9e14-bd392df83eb2"))//agentLogins resource
{
	$auth = 2;
	if(can("update", "4f0ece0c-8e55-45aa-9e14-bd392df83eb2")) //agentLogins resource
	{
		$auth = 1;
	}
	else
	{
		$auth = 2;
	}
//Editing section
if(!empty($_POST['itemId']))
  $itemId = $_POST['itemId'];
else if(isset($_POST['parent']))
{
  $itemId = -1;
  $parent = $_POST['parent'];
}
//else both are unset
	if(isset($_POST['add_parent'])) 
	{
		if($_POST['add_parent'])
		{
		  agent_logins::add_parent($_POST['value']);
		}
	}
	else if (isset($_POST['add_child']))
	{
		if($_POST['add_child'])
		{
		  agent_logins::add_child($_POST['parent_id'], $_POST['label'], $_POST['value']);
		}
	}
	else if (isset($_POST['edit_item']))
	{
		if($_POST['edit_item'])
		{
		  agent_logins::edit_item($_POST['edit_itemId'], $_POST['label'], $_POST['value']);
		}
	}
	else if (isset($_POST['delete']))
	{
		if($_POST['delete'])
		{
		  agent_logins::delete_item($_POST['id']);
		}
	}

function add_tags($id, $parent, $contents)
{
  global $auth;

  if($auth > 1)
    return "<li>$contents</li>";

  $open_tag = '<li>
               <a name="li_'.$id.'">
               <span class="editable" onclick="edit_list_item('.$id.', '.$parent.');">';
  $contents =  $open_tag.$contents.'</span></a>
               <span class="editable ghost_link" onclick="delete_list_item('.$id.', \''.$contents.'\')">(Delete)</span></li>';
  return $contents;
}

function format_list($list, $toplevel = false)
{
  global $auth;
  global $itemId;
  global $parent;
  global $parent_id_global;
  
  echo "\n<ul>\n";
  foreach($list as $item)
  {
    $parent_id = $item['parent'];
    //a check to see if this item is parent to the item being added
    $is_parent_of_add = ($parent == $item['itemId'] && $itemId == -1);
    
    if($item['itemId'] == $itemId && $auth == 1)
    {
      echo '<li><a name="li_'.$itemId.'" style="text-decoration:none;">
            <form method="post">
            <input type="hidden" name="edit_itemId" value="'.$itemId.'">
            <input type="text" name="label" value="'.$item['label'].'"> :
            <input type="text" name="value" value="'.$item['value'].'">
            <input type="submit" name="edit_item" value="Save">
            <input type="submit" name="cancel" value="Cancel">
            </form>
            </a></li>';
    }
    else
    {
      $text = (strpos($item['value'], 'http://') === 0) ? '<a href="'.$item['value'].'">'.$item['value'].'</a>' : $item['value'];
      $label = (empty($item['label'])) ? $text : $item['label'].': '.$text;
      if($toplevel)
        $label = "<b>$label</b>";
      
      echo add_tags($item['itemId'], $parent_id, $label);
    }
    
    if(!empty($item['children']) || $is_parent_of_add)
      format_list($item['children']);
    else if($auth == 1)
    {
      echo '<ul class="ghost_link">
              <li><span class="editable" onclick="add_list_item('.$item['itemId'].')">Click to add a sublist here</span></li>
            </ul>';
    }
    echo '</li>';
  }
  //add the add child part here
  if($auth == 1 && $itemId == -1 && (!isset($parent_id)|| $parent_id == $parent))
  {
    echo '<li>
          <form method="post">
          <input type="hidden" name="parent_id" value="'.$parent.'">
          <input onfocus="clear_default_message(this);" type="text" style="width:280px"
                 name="label" value="Leave this box blank to not display the colon ->"> :
          <input type="text" name="value">
          <input type="submit" name="add_child" value="Add">
          <input type="submit" name="cancel" value="Cancel">
          </form>
          </li>';
  }
  else if($auth == 1 && !$toplevel)
  {
    echo '<li class="ghost_link"><span class="editable" onclick="add_list_item('.$parent_id.')">Click to add another item here</span></li>';
  }
  echo "</ul>\n";
}

$list = agent_logins::get_list();

?>

    <title>Agent Logins</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    
  <?php if($auth == 1) {?>
    <style type="text/css">
      span.editable
      {
        cursor: pointer;
      }
      li.ghost_link, ul.ghost_link, span.ghost_link
      {
        color: #E0E0D2;
      <?php if(isset($_GET['hide_ghost'])) {
	 if($_GET['hide_ghost'] == 'true'){ ?>
        display: none;
      <?php }
		}
	  ?>
      }
      li.ghost_link:hover, ul.ghost_link:hover, span.ghost_link:hover
      {
        color: #405679;
      }
      #instructions
	{
		position:relative;
		font-weight:bold;
		text-align:center;	
	}
      #addHeading
	{
		position:relative;
		font-weight:bold;
		float:right;
		padding-right:0%;
		top:-160em;
		display:inline;	
	}
	#subTitle
	{
		position:relative;
		text-align:center;
			
	}
    </style>
    <script type="text/javascript">
      var displayAddHeaderFlag = 0;
      function delete_list_item(itemId, contents)
      {
        form = document.forms.delete_item_form;
        if(confirm('Are you sure you want to delete "' + contents + '"?'))
        {
          form.id.value = itemId;
          form.delete.click();
        }
      }

      function edit_list_item(itemId, parent)
      {
        form = document.forms.edit_item_form;
        form.action = '#li_' + ((parent) ? parent : itemId);
        form.itemId.value = itemId;
        form.submit.click();
      }

      function add_list_item(parent_id)
      {
        form = document.forms.add_item_form;
        form.action = '#li_' + parent_id;
        form.parent.value = parent_id;
        form.submit.click();
      }

      function clear_default_message(tb)
      {
        if(tb.value == 'Leave this box blank to not display the colon ->')
          tb.value = '';
      }
      
    </script>
  <?php } ?>
 
  <body style='margin: 10'>
    
    <h1 style="text-align:center;">Agent Logins</h1>
    <div id='subTitle'>Please send feedback if you believe something is missing from this list.</div> 
    <?php if($auth == 1) { ?>
      
         <div id = 'instructions'>Admins: Click on an item to edit it. </div>
	 <br />
    <?php } ?>
    <?php
      if(!empty($list))
        format_list($list, true);
	
      if($auth == 1) { ?>
        <form id="edit_item_form" method="post" style="display:none">
          <input type="hidden" name="itemId">
          <input type="submit" name="submit" value="1">
        </form>
        <form id="add_item_form" method="post" style="display:none">
          <input type="hidden" name="parent">
          <input type="submit" name="submit" value="1">
        </form>
        <form id="delete_item_form" method="post" style="display:none">
          <input type="hidden" name="id">
          <input type="submit" name="delete" value="1">
        </form>
        <br><br>
       <div id='addHeading'>
		 <b>Add Heading</b><br>
		<form method="post">
		  <input type="text" name="value"><br>
		  <input type="submit" name="add_parent" value="Add">
		</form>
	</div>
    <?php }
}
else echo '<br /><br /><h1 style="text-align: center;"> You Do Not Have Rights To View This Page! </h1><br /><h2 style="text-align: center;">If this is an error please speak with your manager to get your rights granted.<br /><br />Thank you for not trying to hack the site!<br />~The OPS Dev Team</h2>';
?>
  </body>

<?php
require('../includes/includeAtEnd.php');
?>

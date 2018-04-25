<?php
/**
 * Created by PhpStorm.
 * User: Tobbi
 * Date: 2018-04-09
 * Time: 10:29
 */

include ("../../../inc/includes.php");

$configs = Config::getConfigurationValues('plugin:childticketmanager' , ['childticketmanager_close_child', 'childticketmanager_resolve_child', 'childticketmanager_hide_tmpl_link']);


if($configs['childticketmanager_close_child'] == 1 && $_POST['tickets_status'] == 6)
{
	echo json_encode(updateChildTickets($_POST['tickets_status'], $_POST['tickets_id'], null));
}
elseif($configs['childticketmanager_resolve_child'] == 1 && $_POST['tickets_status'] == 5)
{
	echo json_encode(updateChildTickets($_POST['tickets_status'], $_POST['tickets_id'], null));
}
else
{
	echo json_encode("");
}

function updateChildTickets($status, $current, $parent)
{
	global $DB;
	$children = getChildTickets($current);
	$retour = [];
	
	if($children != null)
	{
		foreach($children as $tix)
			$retour = array_merge($retour, updateChildTickets($status, $tix, $current));
	}

	$currentDate = new datetime();
	$parent_ticket = new Ticket();
	$parent_ticket->getFromDB($current);
	$updatedFields = ['status', 'solvedate', 'closedate', 'solution'];
	$oldValues = [];

	foreach($updatedFields as $fld)
		$oldValues[$fld] = $parent_ticket->fields[$fld];

	$parent_ticket->fields['status'] = $status;
	
	if($status == 5) 
	{
		$parent_ticket->fields['solvedate'] = $currentDate->format("Y-m-d H:i:s");
		if($parent_ticket->fields['solution'] == null && $parent != null)
			$parent_ticket->fields['solution'] = "Résolu par le biais du billet " . $parent;
	}
	elseif($status == 6)
	{
		$parent_ticket->fields['closedate'] = $currentDate->format("Y-m-d H:i:s");

		if($parent_ticket->fields['solvedate'] == null)
			$parent_ticket->fields['solvedate'] = $currentDate->format("Y-m-d H:i:s");
	}
	
	$parent_ticket->updateInDB($updatedFields, $oldValues);
	return array_merge( [$current], $retour );
}

function getChildTickets($parent_id)
{
	global $DB;
	
	$query = "SELECT tickets_id_1 as ticket_id FROM glpi_tickets_tickets WHERE tickets_id_2 = ? AND LINK = 3";
	$stmt = $DB->prepare($query);

	$stmt->bind_param('i', $parent_id);
	$stmt->execute();

	$res = $stmt->get_result();

	if($res->num_rows == 0)
		return null;

	return array_column($res->fetch_all(MYSQLI_ASSOC), 'ticket_id');
	
}
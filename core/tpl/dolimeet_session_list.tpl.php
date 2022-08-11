<?php
if (!empty($fromtype)) {
print dol_get_fiche_head($head, $object->type . 'List', $langs->trans($object->type), -1, $objectLinked->picto);
dol_banner_tab($objectLinked, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
}

if ($fromid) {
print '<div class="underbanner clearboth"></div>';
}

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
$param .= '&limit='.urlencode($limit);
}
foreach ($search as $key => $val) {
if (is_array($search[$key]) && count($search[$key])) {
foreach ($search[$key] as $skey) {
$param .= '&search_'.$key.'[]='.urlencode($skey);
}
} else {
$param .= '&search_'.$key.'='.urlencode($search[$key]);
}
}
if ($optioncss != '') {
$param .= '&optioncss='.urlencode($optioncss);
}
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';
// Add $param from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSearchParam', $parameters, $object); // Note that $action and $object may have been modified by hook
$param .= $hookmanager->resPrint;

// List of mass actions available
$arrayofmassactions = array(
//'validate'=>img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Validate"),
//'generate_doc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("ReGeneratePDF"),
//'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
//'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
);
if ($permissiontodelete) {
$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) {
$arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
if ($optioncss != '') {
print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

$fromurl = '';
if (!empty($fromtype)) {
$fromurl = '&fromtype='.$fromtype.'&fromid='.$fromid;
}
$newcardbutton = dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', dol_buildpath('/dolimeet/view/'. $object->type .'/'. $object->type .'_card.php', 1).'?action=create'.$fromurl, '', $permissiontoadd);
$object->picto ='dolimeet32px@dolimeet';
print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);
$object->picto ='dolimeet16px@dolimeet';
// Add code for pre mass action (confirmation or email presend form)
$topicmail = "Send". $object->type ."Ref";
$modelmail = "document";

$objecttmp = new $object->type($db);
$trackid = 'xxxx'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all) {
foreach ($fieldstosearchall as $key => $val) {
$fieldstosearchall[$key] = $langs->trans($val);
}
print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>';
}

$moreforfilter = '';
/*$moreforfilter.='<div class="divsearchfield">';
	$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
	$moreforfilter.= '</div>';*/

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
$moreforfilter .= $hookmanager->resPrint;
} else {
$moreforfilter = $hookmanager->resPrint;
}

if (!empty($moreforfilter)) {
print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
	print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";


	// Fields title search
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
		foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if ($key == 'status') {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (in_array($val['type'], array('timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
		$cssforfield .= ($cssforfield ? ' ' : '').'right';
		}

		if (!empty($arrayfields['t.'.$key]['checked'])) {
		print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
			if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
			print $form->selectarray('search_'.$key, $val['arrayofkeyval'], (isset($search[$key]) ? $search[$key] : ''), $val['notnull'], 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
			} elseif ($key == 'fk_soc') {
			$thirdparty->fetch(0, $search['fk_soc']);
			print '<div class="nowrap">';
				print $form->select_company((!empty(GETPOST('fk_soc')) ? GETPOST('fk_soc') : (GETPOST('fromtype') == 'thirdparty' ? GETPOST('fromid') : '')), 'fk_soc', '', 'SelectThirdParty', 1, 0, array(), 0, 'maxwidth200');
				print '</div>';
			} elseif ($key == 'fk_project') {
			$project->fetch(0, $search['fk_project']);
			print $formproject->select_projects(0, ( ! empty(GETPOST('fk_project')) ? GETPOST('fk_project') :  (GETPOST('fromtype') == 'project' ? GETPOST('fromid') : '')), 'fk_project', 0, 0, 1, 0, 1, 0, 0, '', 1, 0, 'maxwidth200');
			print '<input class="input-hidden-fk_project" type="hidden" name="search_fk_project" value=""/>';
			} elseif ($key == 'fk_contact') {
			$contact->fetch(0, $search['fk_contact']);
			print $form->selectcontacts(0, !empty(GETPOST('fk_contact')) ? GETPOST('fk_contact') : (GETPOST('fromtype') == 'contact' ? GETPOST('fromid') : ''), 'fk_contact', 1);
			print '<input class="input-hidden-fk_project" type="hidden" name="search_fk_contact" value=""/>';
			} elseif ((strpos($val['type'], 'integer:') === 0) || (strpos($val['type'], 'sellist:') === 0)) {
			print $object->showInputField($val, $key, (isset($search[$key]) ? $search[$key] : ''), '', '', 'search_', 'maxwidth125', 1);
			} elseif (!preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
			print '<input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.dol_escape_htmltag(isset($search[$key]) ? $search[$key] : '').'">';
			} elseif (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
			print '<div class="nowrap">';
				print $form->selectDate($search[$key.'_dtstart'] ? $search[$key.'_dtstart'] : '', "search_".$key."_dtstart", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
				print '</div>';
			print '<div class="nowrap">';
				print $form->selectDate($search[$key.'_dtend'] ? $search[$key.'_dtend'] : '', "search_".$key."_dtend", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
				print '</div>';
			}
			print '</td>';
		}
		}
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

		// Fields from hook
		$parameters = array('arrayfields'=>$arrayfields);
		$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print '<td class="liste_titre maxwidthsearch">';
			$searchpicto = $form->showFilterButtons();
			print $searchpicto;
			print '</td>';
		print '</tr>'."\n";


	// Fields title label
	// --------------------------------------------------------------------
	print '<tr class="liste_titre">';
		foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if ($key == 'status') {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (in_array($val['type'], array('timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
		$cssforfield .= ($cssforfield ? ' ' : '').'right';
		}
		if (!empty($arrayfields['t.'.$key]['checked'])) {
		print getTitleFieldOfList($arrayfields['t.'.$key]['label'], 0, $_SERVER['PHP_SELF'], 't.'.$key, '', $param, ($cssforfield ? 'class="'.$cssforfield.'"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield.' ' : ''))."\n";
		}
		}
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
		// Hook fields
		$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
		$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ')."\n";
		print '</tr>'."\n";


	// Detect if we need a fetch on each output line
	$needToFetchEachLine = 0;
	if (isset($extrafields->attributes[$object->table_element]['computed']) && is_array($extrafields->attributes[$object->table_element]['computed']) && count($extrafields->attributes[$object->table_element]['computed']) > 0) {
	foreach ($extrafields->attributes[$object->table_element]['computed'] as $key => $val) {
	if (preg_match('/\$object/', $val)) {
	$needToFetchEachLine++; // There is at least one compute field that use $object
	}
	}
	}


	// Loop on record
	// --------------------------------------------------------------------
	$i = 0;
	$totalarray = array();
	$totalarray['nbfield'] = 0;
	while ($i < ($limit ? min($num, $limit) : $num)) {
	$obj = $db->fetch_object($resql);
	if (empty($obj)) {
	break; // Should not happen
	}

	// Store properties in $object
	$object->setVarsFromFetchObj($obj);

	// Show here line of result
	print '<tr class="oddeven">';
		foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif ($key == 'status') {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
		}

		if (in_array($val['type'], array('timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		} elseif ($key == 'ref') {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		}

		if (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && !in_array($key, array('rowid', 'status')) && empty($val['arrayofkeyval'])) {
		$cssforfield .= ($cssforfield ? ' ' : '').'right';
		}
		//if (in_array($key, array('fk_soc', 'fk_user', 'fk_warehouse'))) $cssforfield = 'tdoverflowmax100';

		if (!empty($arrayfields['t.'.$key]['checked'])) {
		print '<td'.($cssforfield ? ' class="'.$cssforfield.'"' : '') .'>';
		if ($key == 'fk_soc') {
		$thirdparty->fetch($obj->fk_soc);
		print $thirdparty->getNomUrl(1);
		}
		else if ($key == 'fk_contact') {
		$contact->fetch($obj->fk_contact);
		print $contact->getNomUrl(1);
		}
		else if ($key == 'sender') {
		$sender->fetch($obj->sender);
		print $sender->getNomUrl();
		}
		else if ($key == 'fk_project') {
		$project->fetch($obj->fk_project);
		print $project->getNomUrl(1);
		}
		else if ($key == 'status') {
		print $object->getLibStatut(5);
		} else if ($key == 'rowid') {
		print $object->showOutputField($val, $key, $object->id, '');
		} else {
		print $object->showOutputField($val, $key, $object->$key, '');
		}
		print '</td>';
		if (!$i) {
		$totalarray['nbfield']++;
		}
		if (!empty($val['isameasure'])) {
		if (!$i) {
		$totalarray['pos'][$totalarray['nbfield']] = 't.'.$key;
		}
		if (!isset($totalarray['val'])) {
		$totalarray['val'] = array();
		}
		if (!isset($totalarray['val']['t.'.$key])) {
		$totalarray['val']['t.'.$key] = 0;
		}
		$totalarray['val']['t.'.$key] += $object->$key;
		}
		}
		}
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
		// Fields from hook
		$parameters = array('arrayfields'=>$arrayfields, 'object'=>$object, 'obj'=>$obj, 'i'=>$i, 'totalarray'=>&$totalarray);
		$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print '<td class="nowrap center">';
			if ($massactionbutton || $massaction) { // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			$selected = 0;
			if (in_array($object->id, $arrayofselected)) {
			$selected = 1;
			}
			print '<input id="cb'.$object->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$object->id.'"'.($selected ? ' checked="checked"' : '').'>';
			}
			print '</td>';
		if (!$i) {
		$totalarray['nbfield']++;
		}

		print '</tr>'."\n";

	$i++;
	}

	// Show total line
	include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

	// If no record found
	if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) {
	if (!empty($val['checked'])) {
	$colspan++;
	}
	}
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
	}


	$db->free($resql);

	$parameters = array('arrayfields'=>$arrayfields, 'sql'=>$sql);
	$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print '</table>'."\n";
	print '</div>'."\n";

print '</form>'."\n";

if (in_array('builddoc', $arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords)) {
$hidegeneratedfilelistifempty = 1;
if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) {
$hidegeneratedfilelistifempty = 0;
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
$formfile = new FormFile($db);

// Show list of available documents
$urlsource = $_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
$urlsource .= str_replace('&amp;', '&', $param);

$filedir = $diroutputmassaction;
$genallowed = $permissiontoread;
$delallowed = $permissiontoadd;

print $formfile->showdocuments('massfilesarea_' .$object->type, '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
}

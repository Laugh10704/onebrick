<?php

/**
* Implements of hook_services_resources().
*/
function brick_services_resources() {
  $api = array(
    'event' => array(
      'operations' => array(
        'index' => array(
          'help' => 'Retrieves latest events',
          'callback' => 'brick_event_retrieve',
          'access callback' => 'brick_access',
          'access arguments' => array(),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'chapter',
              'type' => 'int',
              'description' => 'chapter events belong to',
              'source' => array('param' => 'chapter'),
              'optional' => TRUE,
              'default' => '0',
            ),
            array(
              'name' => 'nitems',
              'type' => 'int',
              'description' => 'Number of latest items to get',
              'source' => array('param' => 'nitems'),
              'optional' => TRUE,
              'default' => '10',
            ),
          ),
        ),
        'retrieve' => array(
          'help' => 'Retrieves a full event record',
          'callback' => 'brick_event_retrieve_full',
          'access callback' => 'brick_access',
          'access arguments' => array(),
          'access arguments append' => TRUE,
          'args' => array(
            array(
              'name' => 'nid',
              'type' => 'int',
              'description' => 'event id',
              'source' => array('path' => 0),
              'optional' => FALSE,
              'default' => '0',
            ),
          ),
        ),
      ),
    ),
  );
  return $api;
}

function brick_event_retrieve_full($nid) {
// select the events using a basic query
  $query = db_select('node', 'n')->distinct();
  
  select_basic_event_info($query);

  $query->join('field_data_body', 'bd', 'bd.entity_id = n.nid');
  $query->fields('bd', array('body_value'));

  $query->condition('n.nid', $nid, '=');

  $items = $query->execute()->fetchAll();

  if (count($items)> 0) {
  	append_rsvp_data($items);

  	return $items[0];
  }
 
  return NULL;
}

function select_basic_event_info($query) {
  $query->join('field_data_field_event_date', 'd', 'n.nid = d.entity_id');
  $query->join('field_data_field_event_chapter', 'c', 'n.nid = c.entity_id');
  $query->join('field_data_field_event_site', 'es', 'n.nid = es.entity_id');
  $query->join('field_data_field_event_max_rsvp_capacity', 'mxc', 'n.nid = mxc.entity_id');
  $query->join('node', 'esn', 'esn.nid = es.field_event_site_nid');
  $query->join('location_instance', 'loci', 'loci.nid = esn.nid');
  $query->join('location', 'loc', 'loc.lid = loci.lid');
  $query->fields('n', array('title', 'nid'));
  $query->fields('esn', array('title'));
  $query->fields('d', array('field_event_date_value', 'field_event_date_value2'));
  $query->fields('mxc', array('field_event_max_rsvp_capacity_value'));
  $query->addExpression("CONCAT(loc.street, ', ', loc.city, ' ', loc.postal_code)", "address");
}

function brick_event_retrieve($chapter, $nitems) {
  $nitems = intval($nitems);
  $chapter = intval($chapter);

  // select the events using a basic query
  $query = db_select('node', 'n')->distinct();

  select_basic_event_info($query);

  $query->condition('n.type', 'event', '=');
  
  if ($chapter) {
     $query->condition('c.field_event_chapter_nid', $chapter, '=');
  }
  
  // must be newer than now
  $query->where('d.field_event_date_value2 > CURDATE()');
  $query->orderBy('d.field_event_date_value', 'ASC');

  // Limited by items?
  if ($nitems) {
    $query->range(0, $nitems);
  }
  $items = $query->execute()->fetchAll();
 
  append_rsvp_data($items);
 
  return $items;   
}

function append_rsvp_data($items) {
  $nidToItem = array();

  foreach ($items as $record) {
     $nidToItem[$record->nid] = $record;
  }

  $nidStr = implode(",", array_keys($nidToItem));

  // get the number of rsvps for each event
  $q = "SELECT  field_data_field_rsvp_event.field_rsvp_event_nid as nid, count(field_data_field_rsvp_event.entity_id) as cnt FROM field_data_field_rsvp_person 
                LEFT JOIN field_data_field_rsvp_event ON field_data_field_rsvp_person.entity_id = field_data_field_rsvp_event.entity_id
                INNER JOIN node on node.nid = field_data_field_rsvp_event.entity_id
                WHERE node.status and field_data_field_rsvp_event.field_rsvp_event_nid in ($nidStr)
                GROUP BY field_data_field_rsvp_event.field_rsvp_event_nid";

  $rsvpCnts = db_query($q);

  foreach ($rsvpCnts as $rsvpDat) {
     $nidToItem[$rsvpDat->nid]->rsvpCnt = $rsvpDat->cnt;
  }
}

?>
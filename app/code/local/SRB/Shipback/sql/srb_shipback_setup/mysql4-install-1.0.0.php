<?php

$installer = $this;
$installer->startSetup();
$table_mapper = $installer->getConnection()
    ->newTable($installer->getTable('srb_shipback/srbmapper'))
    ->addColumn('id_srb_map', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'id_srb_map')
    ->addColumn('id_item_srb', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'id_item_srb')
    ->addColumn('id_item', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true
        ), 'Srbid')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'type')
    ->addColumn('last_sent_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false
        ), 'last_sent_at');


$table_shipback = $installer->getConnection()
    ->newTable($installer->getTable('srb_shipback/srbshipback'))
    ->addColumn('id_srb_shipback', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        //'identity'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'id_srb_shipback')
    ->addColumn('id_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false
        ), 'id_order')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'state')
    ->addColumn('mode', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'mode')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false
        ), 'created_at')
    ->addColumn('public_url', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'public_url');
$table_config = $installer->getConnection()
    ->newTable($installer->getTable('srb_shipback/srbconf'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'id')
    ->addColumn('confkey', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'key')
    ->addColumn('confvalue', Varien_Db_Ddl_Table::TYPE_TEXT,255, array(
        'length' => 255,
        'nullable'  => false
        ), 'key');

$installer->getConnection()->createTable($table_config);
$installer->getConnection()->createTable($table_mapper);
$installer->getConnection()->createTable($table_shipback);
Mage::helper("srb_shipback/config")->initConfig();

/*
//clearn table 
DROP table shoprunback_shipback;
DROP TABLE shoprunback_mapper;
DROP TABLE shoprunback_conf;
delete from core_resource WHERE code ="srb_shipback_setup";

*/



//$installer->endSetup();


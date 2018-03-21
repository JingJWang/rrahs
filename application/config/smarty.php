<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$config['cache_lifetime'] = 60;
$config['caching'] = false;
$config['template_dir'] = './view';
$config['compile_dir'] = './template_c';
$config['cache_dir'] =  './cache';
$config['config_dir'] = './application/libraries/config/smarty';
$config['use_sub_dirs'] = false; //子目录变量(是否在缓存文件夹中生成子目录)
$config['left_delimiter'] = '{';
$config['right_delimiter'] = '}';
<?php

class Backup_Folder_Trim{
static function Trim($File) {
   return '/'.trim($File,'/');
}
}
<?php
class Backup_Folder_File_Copy
{
    
    public function CopyDir($From,$To){
        $From = Backup_Folder_Trim::Trim($From);
        $To = Backup_Folder_Trim::Trim($To);
        Backup_Exec::run("cp -rv '$To' '$From'");
    }
}

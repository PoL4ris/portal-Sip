<?php

namespace App\Extensions\Backup;

/**
 * Backup Interface
 */
interface BackupInterface
{
   
    public function backupPreCheck();
    
    public function backupAll();
    
    public function getBackupStatus();
    
}
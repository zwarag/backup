#!/bin/bash
#backupscript for simple server to local copy
#USAGE		sh copy.sh port user pwd hostname fromPath toPath
#USAGE example: sh copy.sh 222 root pwd myhostname /var/www/website /backup/
#Function start#
function writeLog {
	cat "`date` $pMessagei \n"
}
#Function end#

#create logfile if not exists
if [ -f !/var/log/backup.log ] then
	touch /var/log/backup.log
fi


writeLog "Starting backup for copy from $host:$file"
command = "scp -r -P$port $user:$password@$host:$from $to"
#execute command and put the STDERR(the strange 2 after $command) into the binbucket!
$command 2>/dev/null

#if the return value($?) is 0
if (( $? == 0 )); then
    writeLog "Command was successful.."
else
    writeLog "!! THERE WAS AN ERROR WHILE COPYING FROM $host:$from !!"
fi

writeLog "Backup finished.."

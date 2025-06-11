#!/bin/bash

# Daily full backup at 1 AM
0 1 * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --type=full

# Hourly incremental backups
0 * * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --type=incremental

# Critical data continuous backup (every 5 minutes)
*/5 * * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --type=critical

# Backup verification (every 6 hours)
0 */6 * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --verify

# Clean old backups (daily at 3 AM)
0 3 * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --cleanup

# Generate backup report (daily at 6 AM)
0 6 * * * php /Users/mezbjen/Desktop/meschain-sync-enterprise-1/upload/system/library/meschain/helper/backup.php --report

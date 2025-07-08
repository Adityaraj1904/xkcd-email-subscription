#!/bin/bash
# This script should set up a CRON job to run cron.php every 24 hours.
# You need to implement the CRON setup logic here.
#!/bin/bash

# Get absolute path to current directory
DIR=$(cd "$(dirname "$0")" && pwd)

# Add CRON job to run every 24 hours (at 9 AM)
CRON_JOB="0 9 * * * php $DIR/cron.php"

# Check and append the CRON job if it doesn't already exist
(crontab -l 2>/dev/null | grep -v -F "$DIR/cron.php"; echo "$CRON_JOB") | crontab -

echo "CRON job installed successfully to run daily at 9 AM!"

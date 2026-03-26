#!/bin/bash

set -e  # Stop the script if anything fails

# Exact Hostinger paths for your account
REPO_DIR="/home/u982056599/repositories/peal-paced"
PUBLIC_DIR="/home/u982056599/public_html"
BACKUP_DIR="/home/u982056599/public_html_backup_$(date +%Y%m%d_%H%M%S)"
LOG_FILE="/home/u982056599/deploy_log.txt"

echo "---- DEPLOY START $(date) ----" >> $LOG_FILE

# 1. Backup current live site
if [ -d "$PUBLIC_DIR" ]; then
    echo "Backing up current site to $BACKUP_DIR" >> $LOG_FILE
    cp -r $PUBLIC_DIR $BACKUP_DIR
fi

# 2. Clear public_html
echo "Clearing public_html" >> $LOG_FILE
rm -rf $PUBLIC_DIR/*
mkdir -p $PUBLIC_DIR

# 3. Copy new build
echo "Copying new dist build" >> $LOG_FILE
cp -r $REPO_DIR/dist/* $PUBLIC_DIR/

# 4. Finish
echo "Deployment complete." >> $LOG_FILE
echo "---- DEPLOY END $(date) ----" >> $LOG_FILE

echo "Deployment complete."

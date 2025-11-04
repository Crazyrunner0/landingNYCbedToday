# Backup & Recovery Procedures

Comprehensive guide to backing up and recovering NYCBedToday data and configuration.

## Table of Contents

1. [Backup Strategy](#backup-strategy)
2. [Database Backups](#database-backups)
3. [Uploads & Media Backups](#uploads--media-backups)
4. [Complete System Backups](#complete-system-backups)
5. [Recovery Procedures](#recovery-procedures)
6. [Testing & Validation](#testing--validation)
7. [Offsite Storage](#offsite-storage)

---

## Backup Strategy

### Retention Policy

| Backup Type | Frequency | Retention | Priority |
|-------------|-----------|-----------|----------|
| Database | Daily @ 2 AM | 30 days | **Critical** |
| Uploads | Weekly (Sunday) | 12 weeks | High |
| Full System | Monthly (1st) | 6 months | Medium |
| Pre-Deployment | On-demand | Until post-verified | Critical |

### Backup Locations

- **Primary**: Local server (`/home/backups/`)
- **Secondary**: AWS S3 / Google Cloud Storage (encrypted)
- **Tertiary**: Separate backup service (UpdraftPlus, Backblaze, etc.)

### RPO (Recovery Point Objective)
- **Database**: 24 hours (daily backups)
- **Uploads**: 7 days (weekly backups)
- **Files**: 30 days (daily for pre-deployment, weekly for others)

### RTO (Recovery Time Objective)
- **Database**: 1 hour (restore from local backup)
- **Uploads**: 2 hours (restore from cloud storage)
- **Full System**: 4 hours (re-provision and restore)

---

## Database Backups

### Automated Daily Backups

1. **Create backup script** (`/home/backup/backup-database.sh`):

```bash
#!/bin/bash
set -e

# Configuration
DB_NAME="nycbedtoday_prod"
DB_USER="nycbed_prod"
DB_PASSWORD="${DB_PASSWORD}"
BACKUP_DIR="/home/backups/database"
ARCHIVE_DIR="/home/backups/archive"
S3_BUCKET="your-backup-bucket"
S3_PATH="nycbedtoday/database"
RETENTION_DAYS=30

# Create directories
mkdir -p $BACKUP_DIR
mkdir -p $ARCHIVE_DIR

# Backup filename
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup-${DATE}.sql"
BACKUP_COMPRESSED="${BACKUP_FILE}.gz"

# Perform backup
echo "Starting database backup at $(date)"

if mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$BACKUP_FILE"; then
    echo "✓ Database dumped successfully"
    
    # Compress backup
    gzip "$BACKUP_FILE"
    echo "✓ Backup compressed"
    
    # Verify file
    if [ -f "$BACKUP_COMPRESSED" ]; then
        FILE_SIZE=$(du -h "$BACKUP_COMPRESSED" | cut -f1)
        echo "✓ Backup file created: $FILE_SIZE"
    else
        echo "✗ Failed to create compressed backup"
        exit 1
    fi
    
    # Upload to S3
    if command -v aws &> /dev/null; then
        if aws s3 cp "$BACKUP_COMPRESSED" "s3://${S3_BUCKET}/${S3_PATH}/backup-${DATE}.sql.gz" \
           --storage-class STANDARD_IA \
           --sse AES256; then
            echo "✓ Backup uploaded to S3"
        else
            echo "✗ Failed to upload to S3"
        fi
    fi
    
    # Clean up old local backups
    find "$BACKUP_DIR" -name "backup-*.sql.gz" -mtime +$RETENTION_DAYS -delete
    echo "✓ Cleaned up backups older than $RETENTION_DAYS days"
    
    # Log successful backup
    echo "Backup completed successfully at $(date)" >> /var/log/nycbedtoday-backup.log
    
    # Send notification (optional)
    echo "Database backup completed successfully. Size: $FILE_SIZE" | mail -s "NYCBedToday Backup Success" backups@example.com
else
    echo "✗ Database backup failed"
    exit 1
fi
```

2. **Add to crontab**:

```bash
crontab -e

# Add this line:
0 2 * * * /home/backup/backup-database.sh >> /var/log/nycbedtoday-backup.log 2>&1
```

3. **Make script executable**:

```bash
chmod +x /home/backup/backup-database.sh
```

### Manual Database Backup

Backup database immediately:

```bash
# Using WP-CLI (recommended)
make wp CMD='db export backup-$(date +%Y%m%d-%H%M%S).sql'

# Or using mysqldump directly
mysqldump -u nycbed_prod -p nycbedtoday_prod > backup-$(date +%Y%m%d-%H%M%S).sql

# Compress
gzip backup-*.sql

# Upload to cloud storage
aws s3 cp backup-*.sql.gz s3://your-bucket/nycbedtoday/database/
```

### Backup Verification

```bash
# List recent backups
ls -lh /home/backups/database/ | tail -10

# Check backup integrity
gunzip -t backup-*.sql.gz  # Should return no error

# Count tables in backup
zcat backup-*.sql.gz | grep "CREATE TABLE" | wc -l
```

---

## Uploads & Media Backups

### Automated Weekly Backups

1. **Create uploads backup script** (`/home/backup/backup-uploads.sh`):

```bash
#!/bin/bash
set -e

# Configuration
UPLOADS_DIR="/var/www/app/web/app/uploads"
BACKUP_DIR="/home/backups/uploads"
S3_BUCKET="your-backup-bucket"
S3_PATH="nycbedtoday/uploads"
RETENTION_WEEKS=12

mkdir -p $BACKUP_DIR

DATE=$(date +%Y%m%d)
BACKUP_FILE="$BACKUP_DIR/uploads-${DATE}.tar.gz"

echo "Starting uploads backup at $(date)"

# Create tar archive
if tar -czf "$BACKUP_FILE" -C $(dirname $UPLOADS_DIR) $(basename $UPLOADS_DIR) 2>/dev/null; then
    echo "✓ Uploads archived"
    
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "✓ Archive size: $FILE_SIZE"
    
    # Upload to S3
    if aws s3 cp "$BACKUP_FILE" "s3://${S3_BUCKET}/${S3_PATH}/uploads-${DATE}.tar.gz" \
       --storage-class GLACIER \
       --sse AES256; then
        echo "✓ Uploaded to S3"
    fi
    
    # Clean old local backups
    find "$BACKUP_DIR" -name "uploads-*.tar.gz" -mtime +$((RETENTION_WEEKS * 7)) -delete
    
    echo "Uploads backup completed at $(date)" >> /var/log/nycbedtoday-backup.log
else
    echo "✗ Failed to create uploads archive"
    exit 1
fi
```

2. **Add to crontab** (every Sunday at 3 AM):

```bash
crontab -e

# Add:
0 3 * * 0 /home/backup/backup-uploads.sh >> /var/log/nycbedtoday-backup.log 2>&1
```

### Manual Uploads Backup

```bash
# Create backup
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz /var/www/app/web/app/uploads

# Upload to S3
aws s3 cp uploads-backup-*.tar.gz s3://your-bucket/nycbedtoday/uploads/

# Verify archive
tar -tzf uploads-backup-*.tar.gz | head -20
```

---

## Complete System Backups

### Monthly Full System Backup

Create script (`/home/backup/backup-full-system.sh`):

```bash
#!/bin/bash
set -e

# Configuration
APP_DIR="/var/www/app"
BACKUP_DIR="/home/backups/full-system"
S3_BUCKET="your-backup-bucket"
S3_PATH="nycbedtoday/full-system"
RETENTION_MONTHS=6

mkdir -p $BACKUP_DIR

DATE=$(date +%Y%m%d)
BACKUP_FILE="$BACKUP_DIR/full-system-${DATE}.tar.gz"

echo "Starting full system backup at $(date)"

# Exclude unnecessary files
EXCLUDE_OPTS="--exclude=.git \
              --exclude=node_modules \
              --exclude=vendor \
              --exclude=.env \
              --exclude=web/app/uploads \
              --exclude=web/app/cache \
              --exclude=*.log \
              --exclude=.DS_Store"

# Create backup
if tar -czf "$BACKUP_FILE" $EXCLUDE_OPTS -C /var/www app 2>/dev/null; then
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "✓ Full system backup created: $FILE_SIZE"
    
    # Upload to S3 (use Glacier for cost)
    if aws s3 cp "$BACKUP_FILE" "s3://${S3_BUCKET}/${S3_PATH}/full-system-${DATE}.tar.gz" \
       --storage-class GLACIER \
       --sse AES256; then
        echo "✓ Backup uploaded to S3"
    fi
    
    # Clean old backups
    find "$BACKUP_DIR" -name "full-system-*.tar.gz" -mtime +$((RETENTION_MONTHS * 30)) -delete
    
    echo "Full system backup completed at $(date)" >> /var/log/nycbedtoday-backup.log
else
    echo "✗ Full system backup failed"
    exit 1
fi
```

Add to crontab (first of month at 4 AM):

```bash
0 4 1 * * /home/backup/backup-full-system.sh >> /var/log/nycbedtoday-backup.log 2>&1
```

---

## Recovery Procedures

### Database Recovery

#### Scenario 1: Restore Latest Daily Backup

```bash
# 1. Stop WordPress
cd /var/www/app
make down

# 2. List available backups
ls -lh /home/backups/database/

# 3. Restore from backup
BACKUP_FILE="/home/backups/database/backup-20241215_020000.sql.gz"

# Decompress and restore
gunzip < $BACKUP_FILE | mysql -u nycbed_prod -p nycbedtoday_prod

# Or using WP-CLI
make wp CMD="db import $BACKUP_FILE"

# 4. Start WordPress
make up

# 5. Verify database
make wp CMD='db check --repair'

# 6. Clear caches
make wp CMD='cache flush'
make wp CMD='redis flush'

# 7. Verify site
curl https://nycbedtoday.com
```

#### Scenario 2: Restore from S3 Backup

```bash
# 1. List S3 backups
aws s3 ls s3://your-bucket/nycbedtoday/database/

# 2. Download specific backup
aws s3 cp s3://your-bucket/nycbedtoday/database/backup-20241215_020000.sql.gz \
  /tmp/backup-restore.sql.gz

# 3. Restore
gunzip < /tmp/backup-restore.sql.gz | mysql -u nycbed_prod -p nycbedtoday_prod

# 4. Verify
make wp CMD='db check'

# 5. Clean up
rm /tmp/backup-restore.sql.gz
```

#### Scenario 3: Restore Specific Table

```bash
# Extract single table from backup
gunzip < /home/backups/database/backup-20241215.sql.gz | \
  grep "CREATE TABLE \`wp_posts\`" -A 1000 | \
  grep "CREATE TABLE" -B 1000 | \
  head -n -1 > wp_posts_backup.sql

# Restore table
mysql -u nycbed_prod -p nycbedtoday_prod < wp_posts_backup.sql

# Verify
make wp CMD='post list --count'
```

### Uploads Recovery

#### Restore Latest Uploads Backup

```bash
# 1. Stop WordPress
make down

# 2. Download backup
aws s3 cp s3://your-bucket/nycbedtoday/uploads/uploads-20241215.tar.gz /tmp/

# 3. Restore (preserving current structure first)
cp -r /var/www/app/web/app/uploads /var/www/app/web/app/uploads.backup

# Extract to root (creates app/uploads structure)
tar -xzf /tmp/uploads-20241215.tar.gz -C /

# 4. Verify permissions
chown -R www-data:www-data /var/www/app/web/app/uploads
chmod -R 755 /var/www/app/web/app/uploads

# 5. Start WordPress
make up

# 6. Verify in WordPress
make wp CMD='attachment list --count'

# 7. If issues, restore backup
rm -rf /var/www/app/web/app/uploads
mv /var/www/app/web/app/uploads.backup /var/www/app/web/app/uploads
```

### Full System Recovery

#### Restore Full System Backup

```bash
# 1. Create new server with same specs

# 2. Install dependencies
apt-get update && apt-get install -y \
  docker.io docker-compose mysql-client \
  nginx php-fpm php-mysql php-json

# 3. Download backup
aws s3 cp s3://your-bucket/nycbedtoday/full-system/full-system-20241215.tar.gz /tmp/

# 4. Extract
tar -xzf /tmp/full-system-20241215.tar.gz -C /var/www

# 5. Restore .env (from secure storage)
# Copy production .env from GitHub secrets or password manager

# 6. Restore database
# (Follow database recovery steps above)

# 7. Start services
cd /var/www/app
make up

# 8. Verify
make healthcheck
```

---

## Testing & Validation

### Weekly Backup Test

Every Monday, test a backup restore:

```bash
#!/bin/bash
# Test backup restoration

echo "Starting backup test..."

# 1. Download latest backup
aws s3 cp s3://your-bucket/nycbedtoday/database/backup-latest.sql.gz /tmp/test-backup.sql.gz

# 2. Create test database
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS nycbedtoday_test;"

# 3. Restore to test DB
gunzip < /tmp/test-backup.sql.gz | \
  mysql -u root -p$MYSQL_ROOT_PASSWORD nycbedtoday_test

# 4. Verify
TEST_POSTS=$(mysql -u root -p$MYSQL_ROOT_PASSWORD -se \
  "SELECT COUNT(*) FROM nycbedtoday_test.wp_posts WHERE post_type='post'")

echo "Posts in backup: $TEST_POSTS"

# 5. Clean up
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "DROP DATABASE nycbedtoday_test;"
rm /tmp/test-backup.sql.gz

echo "✓ Backup test completed"
```

Add to crontab (Mondays at 5 AM):

```bash
0 5 * * 1 /home/backup/test-backup.sh >> /var/log/backup-test.log 2>&1
```

### Monthly Restore Drill

Monthly (first Sunday):

1. Document current state
2. Perform full database restore to test system
3. Verify all data present
4. Test critical functionality (checkout, admin login)
5. Document any issues
6. Clean up test environment
7. Report results to team

---

## Offsite Storage

### AWS S3 Configuration

1. **Create S3 bucket**:

```bash
aws s3 mb s3://nycbedtoday-backups --region us-east-1
```

2. **Set versioning** (enables recovery of previous versions):

```bash
aws s3api put-bucket-versioning \
  --bucket nycbedtoday-backups \
  --versioning-configuration Status=Enabled
```

3. **Enable server-side encryption**:

```bash
aws s3api put-bucket-encryption \
  --bucket nycbedtoday-backups \
  --server-side-encryption-configuration '{
    "Rules": [{
      "ApplyServerSideEncryptionByDefault": {
        "SSEAlgorithm": "AES256"
      }
    }]
  }'
```

4. **Set lifecycle policy** (automatic archiving/deletion):

```bash
aws s3api put-bucket-lifecycle-configuration \
  --bucket nycbedtoday-backups \
  --lifecycle-configuration '{
    "Rules": [
      {
        "Id": "archive-old-backups",
        "Status": "Enabled",
        "Prefix": "",
        "Transitions": [
          {
            "Days": 30,
            "StorageClass": "STANDARD_IA"
          },
          {
            "Days": 90,
            "StorageClass": "GLACIER"
          }
        ],
        "Expiration": {
          "Days": 365
        }
      }
    ]
  }'
```

5. **Create IAM user** for backups:

```bash
# Create user
aws iam create-user --user-name nycbedtoday-backup

# Attach policy
aws iam put-user-policy --user-name nycbedtoday-backup \
  --policy-name BackupPolicy --policy-document '{
    "Version": "2012-10-17",
    "Statement": [
      {
        "Effect": "Allow",
        "Action": ["s3:PutObject", "s3:GetObject", "s3:ListBucket"],
        "Resource": [
          "arn:aws:s3:::nycbedtoday-backups",
          "arn:aws:s3:::nycbedtoday-backups/*"
        ]
      }
    ]
  }'

# Create access key
aws iam create-access-key --user-name nycbedtoday-backup
```

### Configure AWS CLI

```bash
# Setup AWS credentials
mkdir -p /root/.aws
cat > /root/.aws/credentials << EOF
[default]
aws_access_key_id = YOUR_ACCESS_KEY
aws_secret_access_key = YOUR_SECRET_KEY
EOF

# Set permissions
chmod 600 /root/.aws/credentials

# Configure region
aws configure set region us-east-1
```

### Alternative: Google Cloud Storage

```bash
# Create bucket
gsutil mb gs://nycbedtoday-backups/

# Set lifecycle (delete after 1 year)
gsutil lifecycle set - gs://nycbedtoday-backups/ << 'EOF'
{
  "lifecycle": {
    "rule": [
      {
        "action": {"type": "Delete"},
        "condition": {"age": 365}
      }
    ]
  }
}
EOF

# Backup
gsutil -m cp /home/backups/database/*.sql.gz gs://nycbedtoday-backups/database/
```

---

## Monitoring & Alerts

### Check Backup Health

```bash
# Daily backup check script
#!/bin/bash

BACKUP_DIR="/home/backups/database"
MIN_AGE=25  # hours (backup should be less than 25 hours old)
DATE=$(date +%s)

LATEST_BACKUP=$(ls -t $BACKUP_DIR/*.gz | head -1)

if [ -z "$LATEST_BACKUP" ]; then
  echo "✗ No backups found"
  exit 1
fi

BACKUP_AGE=$(( ($DATE - $(stat -c %Y "$LATEST_BACKUP")) / 3600 ))
BACKUP_SIZE=$(du -h "$LATEST_BACKUP" | cut -f1)

if [ $BACKUP_AGE -lt $MIN_AGE ]; then
  echo "✓ Latest backup: $BACKUP_SIZE ($BACKUP_AGE hours old)"
else
  echo "✗ Backup is stale: $BACKUP_AGE hours old"
  exit 1
fi
```

Add to crontab (daily at 3 PM):

```bash
0 15 * * * /home/backup/check-backup-health.sh | mail -s "Backup Health Check" admin@example.com
```

### AWS S3 Backup Monitoring

```bash
# List recent backups
aws s3 ls s3://nycbedtoday-backups/database/ --recursive --human-readable --summarize

# Check backup size trend
aws s3 ls s3://nycbedtoday-backups/database/ --recursive | \
  awk '{print $1, $5, $4}' | tail -10
```

---

## Disaster Recovery Plan

### In Case of Data Loss

1. **Stop all services immediately**
   ```bash
   make down
   # or
   systemctl stop nginx php-fpm mysql
   ```

2. **Assess damage**
   - How much data lost?
   - What was the last good backup?
   - How long can we be down?

3. **Decide recovery strategy**
   - From local backup? (~1 hour)
   - From cloud backup? (~2 hours)
   - Full system rebuild? (~4 hours)

4. **Execute recovery** (see Recovery Procedures above)

5. **Verify thoroughly**
   - All data present
   - All functionality working
   - No data corruption

6. **Communicate status**
   - Update status page
   - Notify customers
   - Document incident

7. **Post-incident review**
   - What went wrong?
   - How to prevent in future?
   - Update procedures

---

## Contact & Escalation

**Backup Issues**: Notify DevOps lead
**Restore Emergency**: Page on-call engineer
**Data Loss**: Escalate to CTO immediately


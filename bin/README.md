# Changelog Update Script

## Overview

The `update-changelog.sh` script automates the process of adding entries to the CHANGELOG.md file. It's used by the GitHub Actions workflow but can also be run locally for testing or manual changelog updates.

## Usage

```bash
./bin/update-changelog.sh <pr_title> <pr_number> <category>
```

### Arguments

1. **pr_title**: The title of the pull request (will be the changelog entry text)
2. **pr_number**: The PR number
3. **category**: The changelog category (one of: Added, Changed, Fixed, Deprecated, Removed, Security)

### Examples

Add a bug fix entry:
```bash
./bin/update-changelog.sh "Fix validation error" "456" "Fixed"
```

Add a new feature:
```bash
./bin/update-changelog.sh "Add support for draft-07" "789" "Added"
```

Add a breaking change:
```bash
./bin/update-changelog.sh "Remove deprecated API" "123" "Changed"
```

## How It Works

The script:
1. Creates a changelog entry in the format: `- PR Title ([#123](url))`
2. Uses awk to parse the CHANGELOG.md file
3. Finds the "Unreleased" section
4. Adds the entry under the appropriate category (creates the category if it doesn't exist)
5. Maintains proper ordering (newest entries first within each category)

## Testing Locally

Before committing CHANGELOG.md changes, you can test the script on a copy:

```bash
# Create a backup
cp CHANGELOG.md CHANGELOG.md.backup

# Test the script
./bin/update-changelog.sh "Test entry" "999" "Fixed"

# Review the changes
git diff CHANGELOG.md

# Restore if needed
mv CHANGELOG.md.backup CHANGELOG.md
```

## Environment Variables

- **GITHUB_REPOSITORY_URL**: Optional. The repository URL for generating PR links. Defaults to `https://github.com/jsonrainbow/json-schema`

Example:
```bash
export GITHUB_REPOSITORY_URL="https://github.com/myorg/myrepo"
./bin/update-changelog.sh "My change" "42" "Fixed"
```

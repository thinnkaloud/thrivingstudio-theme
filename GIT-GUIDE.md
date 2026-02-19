# Git Version Control Guide

## Overview

This WordPress theme is now under Git version control for professional development workflow.

## Quick Start

### Check Status
```bash
git status
```

### View Changes
```bash
git diff
```

### Commit Changes
```bash
git add .
git commit -m "Description of changes"
```

### View History
```bash
git log --oneline
```

## Common Workflows

### 1. Making Theme Updates

1. **Check current status:**
   ```bash
   git status
   ```

2. **Make your changes** to theme files

3. **Stage changes:**
   ```bash
   git add .
   # Or add specific files:
   git add functions.php style.css
   ```

4. **Commit changes:**
   ```bash
   git commit -m "Add new feature: description"
   ```

### 2. Creating Feature Branches

1. **Create and switch to new branch:**
   ```bash
   git checkout -b feature/new-feature-name
   ```

2. **Make changes and commit:**
   ```bash
   git add .
   git commit -m "Add new feature"
   ```

3. **Switch back to main:**
   ```bash
   git checkout main
   ```

4. **Merge feature branch:**
   ```bash
   git merge feature/new-feature-name
   ```

### 3. Rolling Back Changes

**Revert last commit:**
```bash
git revert HEAD
```

**Reset to previous commit:**
```bash
git reset --hard HEAD~1
```

**View specific file version:**
```bash
git show HEAD:functions.php
```

## Branch Strategy

### Main Branch
- **Purpose:** Production-ready code
- **Never commit directly** to main
- **Only merge** from feature branches

### Feature Branches
- **Naming:** `feature/description` (e.g., `feature/adsense-integration`)
- **Purpose:** Develop new features
- **Delete after merging**

### Hotfix Branches
- **Naming:** `hotfix/description` (e.g., `hotfix/security-patch`)
- **Purpose:** Fix critical issues
- **Merge to main immediately**

## Commit Message Guidelines

### Format
```
type(scope): description

[optional body]

[optional footer]
```

### Types
- **feat:** New feature
- **fix:** Bug fix
- **docs:** Documentation changes
- **style:** Code style changes (formatting, etc.)
- **refactor:** Code refactoring
- **test:** Adding tests
- **chore:** Maintenance tasks

### Examples
```
feat(adsense): add Google AdSense integration
fix(seo): correct meta tag generation
docs(readme): update installation instructions
style(css): improve responsive design
```

## Remote Repository Setup

### Connect to GitHub/GitLab

1. **Create repository** on GitHub/GitLab

2. **Add remote:**
   ```bash
   git remote add origin https://github.com/username/repository.git
   ```

3. **Push to remote:**
   ```bash
   git push -u origin main
   ```

### Team Collaboration

1. **Pull latest changes:**
   ```bash
   git pull origin main
   ```

2. **Push your changes:**
   ```bash
   git push origin feature/your-branch
   ```

3. **Create pull request** on GitHub/GitLab

## Useful Commands

### View Information
```bash
git log --oneline -10          # Last 10 commits
git show HEAD                  # Show last commit details
git branch -a                  # List all branches
git remote -v                  # Show remote repositories
```

### Stashing (Temporary Save)
```bash
git stash                      # Save changes temporarily
git stash pop                  # Restore stashed changes
git stash list                 # List stashed changes
```

### Tagging Releases
```bash
git tag v1.0.0                 # Create version tag
git tag -a v1.0.0 -m "Release 1.0.0"  # Annotated tag
git push origin v1.0.0         # Push tag to remote
```

## WordPress Theme Specific

### Files to Watch
- `functions.php` - Core functionality
- `style.css` - Theme styles
- `index.php` - Main template
- `inc/` - Include files
- `template-parts/` - Template components

### Before Committing
1. **Test locally** - Ensure theme works
2. **Check for errors** - PHP syntax, CSS validation
3. **Update version** in `style.css` if needed
4. **Update CHANGELOG.md** with changes

### Deployment Workflow
1. **Create release branch:**
   ```bash
   git checkout -b release/v1.0.0
   ```

2. **Update version numbers:**
   - `style.css` - Theme version
   - `package.json` - Node version
   - `CHANGELOG.md` - Release notes

3. **Test thoroughly**

4. **Merge to main:**
   ```bash
   git checkout main
   git merge release/v1.0.0
   git tag v1.0.0
   ```

5. **Create zip for deployment:**
   ```bash
   git archive --format=zip --output=thrivingstudio-v1.0.0.zip main
   ```

## Troubleshooting

### Common Issues

**"Changes not staged for commit"**
```bash
git add .
git commit -m "Your message"
```

**"Untracked files"**
```bash
git add filename
# Or add all:
git add .
```

**"Merge conflicts"**
1. Open conflicted files
2. Resolve conflicts manually
3. `git add .`
4. `git commit`

**"Wrong commit message"**
```bash
git commit --amend -m "New message"
```

### Reset Repository
```bash
git reset --hard HEAD          # Reset to last commit
git clean -fd                  # Remove untracked files
```

## Best Practices

1. **Commit frequently** - Small, logical commits
2. **Write clear messages** - Describe what and why
3. **Test before committing** - Don't commit broken code
4. **Use branches** - Keep main stable
5. **Pull before pushing** - Avoid conflicts
6. **Review changes** - `git diff` before committing

## Integration with Development

### VS Code
- Install Git extension
- Use Source Control panel
- Enable auto-save

### Terminal Aliases
Add to your shell profile:
```bash
alias gs='git status'
alias ga='git add .'
alias gc='git commit -m'
alias gl='git log --oneline'
alias gp='git push'
alias gpl='git pull'
```

This Git setup provides a professional development workflow for your WordPress theme! 🚀 
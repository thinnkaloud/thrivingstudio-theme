# CSS Development Workflow Guide

## 🎯 Quick Reference

### During Development
```bash
# Start watch mode (auto-rebuilds on CSS changes)
npm run watch
```

### After Making CSS Changes
```bash
# Rebuild CSS manually
npm run build:css

# Or use the rebuild command (includes confirmation)
npm run rebuild
```

### Check CSS Status
```bash
# Check if CSS needs rebuilding
npm run check:css
```

## 📋 Common Workflow

### Scenario 1: Active Development
1. **Start watch mode** in a terminal:
   ```bash
   npm run watch
   ```
2. Edit `frontend/index.css`
3. **Watch mode automatically rebuilds** `frontend/build.css`
4. Refresh browser to see changes

### Scenario 2: Quick CSS Fix
1. Edit `frontend/index.css`
2. Run `npm run build:css`
3. Refresh browser (hard refresh: Cmd+Shift+R / Ctrl+Shift+R)

### Scenario 3: Not Sure if CSS is Built?
1. Check status: `npm run check:css`
2. If outdated, rebuild: `npm run build:css`

## ⚠️ Warning System

### Admin Notice
If you're logged into WordPress admin and CSS source is newer than build, you'll see a warning notice at the top of admin pages.

### Manual Check
Run `npm run check:css` to check build status anytime.

## 🔧 Troubleshooting

### Problem: CSS changes not showing
**Solution:**
1. Check if CSS was rebuilt: `npm run check:css`
2. If outdated, rebuild: `npm run build:css`
3. Hard refresh browser (Cmd+Shift+R / Ctrl+Shift+R)
4. Clear browser cache if needed

### Problem: Watch mode not working
**Solution:**
1. Stop watch mode (Ctrl+C)
2. Restart: `npm run watch`
3. If still not working, manually rebuild: `npm run build:css`

### Problem: CSS still showing old styles after rebuild
**Solution:**
1. Check WordPress cache busting (uses file modification time)
2. Hard refresh browser
3. Clear WordPress/object cache if using caching plugin
4. Check browser DevTools → Network tab to see CSS file timestamp

## 📁 File Structure

```
thrivingstudio/
├── frontend/
│   ├── index.css    ← Edit this file (source)
│   └── build.css    ← Auto-generated (don't edit!)
└── package.json     ← npm scripts defined here
```

## 🎨 Best Practices

1. **Always edit `index.css`**, never `build.css`
2. **Keep watch mode running** during active CSS development
3. **Rebuild before committing** CSS changes to git
4. **Check build status** if styles seem broken
5. **Use hard refresh** (Cmd+Shift+R) after rebuilding

## 🚀 Quick Commands Cheat Sheet

| Command | Purpose |
|---------|---------|
| `npm run watch` | Auto-rebuild on CSS changes |
| `npm run build:css` | Manual rebuild |
| `npm run rebuild` | Rebuild with confirmation |
| `npm run check:css` | Check if rebuild needed |
| `npm run build` | Rebuild CSS + JS |

## 💡 Pro Tips

- **Keep watch mode running** in a separate terminal during development
- **Admin notice** will warn you if CSS is out of sync
- **File modification time** is used for cache busting (automatic)
- **Hard refresh** browser after every rebuild to see changes immediately


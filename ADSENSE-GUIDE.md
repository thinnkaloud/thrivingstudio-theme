# Google AdSense Integration Guide

## Overview

This WordPress theme includes comprehensive Google AdSense integration with the following features:

- ✅ **Admin Settings Panel** - Easy configuration through WordPress admin
- ✅ **Multiple Ad Locations** - Header, content, sidebar, footer, and in-content ads
- ✅ **Responsive Design** - Ads automatically adapt to different screen sizes
- ✅ **Lazy Loading** - Performance-optimized ad loading
- ✅ **Test Mode** - Safe testing without affecting live ads
- ✅ **Page Exclusion** - Control which pages show ads
- ✅ **Custom CSS** - Style ad containers to match your theme
- ✅ **Shortcode Support** - Manual ad placement anywhere
- ✅ **Dark Mode Support** - Ads adapt to theme color scheme

## Quick Setup

### 1. Access AdSense Settings

1. Go to **WordPress Admin** → **Settings** → **AdSense Settings**
2. Enter your **AdSense Publisher ID** (found in your AdSense account)
3. Check **"Enable AdSense"** to activate ads
4. Save settings

### 2. Configure Ad Units

For each ad location, you'll need to create ad units in your AdSense account and add their IDs:

- **Header Ad Unit ID** - Displays at the top of pages
- **Content Ad Unit ID** - Displays in main content area
- **Sidebar Ad Unit ID** - Displays in sidebar/category menu area
- **Footer Ad Unit ID** - Displays at the bottom of pages
- **In-Content Ad Unit ID** - Automatically inserted within post content

### 3. Test Your Setup

1. Enable **Test Mode** to see placeholder ads
2. Visit your website to verify ad placement
3. Disable test mode when ready for live ads

## Ad Locations

### Automatic Placement

Ads are automatically displayed in these locations:

- **Header**: After the main navigation
- **Content**: Before the main content area
- **Sidebar**: Above the category menu
- **Footer**: Before the footer
- **In-Content**: Automatically inserted within blog posts

### Manual Placement

Use shortcodes to place ads anywhere:

```
[adsense ad="header"]
[adsense ad="content"]
[adsense ad="sidebar"]
[adsense ad="footer"]
[adsense ad="in-content"]
```

### Custom Placement

Use action hooks in your theme files:

```php
<?php do_action('thrivingstudio_after_header'); ?>
<?php do_action('thrivingstudio_before_content'); ?>
<?php do_action('thrivingstudio_sidebar_top'); ?>
<?php do_action('thrivingstudio_before_footer'); ?>
```

## Advanced Configuration

### Test Mode

Enable test mode to:
- Show placeholder ads instead of live ads
- Test ad placement without affecting revenue
- Develop and debug safely

### Lazy Loading

Lazy loading improves page performance by:
- Loading ads only when they come into view
- Reducing initial page load time
- Improving Core Web Vitals scores

### Page Exclusion

Exclude specific pages from showing ads:
- Enter page IDs or slugs (one per line)
- Examples: `about-us`, `contact`, `123`
- Useful for privacy policy, terms of service, etc.

### Custom CSS

Add custom CSS to style ad containers:

```css
.adsense-header-ad {
    background: #f0f0f0;
    border-radius: 8px;
    padding: 20px;
}

.adsense-content-ad {
    margin: 30px 0;
    text-align: center;
}
```

## In-Content Ad Positioning

Control where in-content ads appear within blog posts:

- **After 2nd paragraph** - Early engagement
- **Middle of content** - Balanced placement
- **After 3rd paragraph** - More content before ad
- **Before last paragraph** - End of content

## Performance Optimization

### Built-in Optimizations

- **Lazy Loading**: Ads load only when visible
- **Async Loading**: AdSense script loads asynchronously
- **Responsive Design**: Ads adapt to screen size
- **Minimal CSS**: Lightweight styling

### Best Practices

1. **Don't overload pages** - Use 2-3 ads per page maximum
2. **Respect user experience** - Don't place ads too close together
3. **Test on mobile** - Ensure ads work well on all devices
4. **Monitor performance** - Use Google PageSpeed Insights

## Troubleshooting

### Ads Not Showing

1. Check if AdSense is enabled in settings
2. Verify Publisher ID is correct
3. Ensure page is not in exclusion list
4. Check browser console for errors
5. Verify AdSense account is approved

### Test Mode Issues

1. Clear browser cache
2. Check if test mode is enabled
3. Verify ad unit IDs are entered
4. Check WordPress debug log

### Performance Issues

1. Enable lazy loading
2. Reduce number of ads per page
3. Use responsive ad units
4. Monitor Core Web Vitals

## AdSense Policy Compliance

### Important Guidelines

- **Don't place ads too close to navigation**
- **Maintain clear separation between ads and content**
- **Don't exceed 3 ads per page**
- **Ensure ads are clearly labeled**
- **Don't place ads in pop-ups or overlays**

### Recommended Practices

- Use responsive ad units
- Test on multiple devices
- Monitor ad performance
- Follow AdSense program policies
- Keep content-to-ad ratio balanced

## Support

### Getting Help

1. **AdSense Account**: Visit [Google AdSense Help](https://support.google.com/adsense)
2. **Theme Issues**: Check WordPress debug log
3. **Performance**: Use Google PageSpeed Insights
4. **Policy Questions**: Review [AdSense Program Policies](https://support.google.com/adsense/answer/48182)

### Useful Links

- [Google AdSense](https://www.google.com/adsense)
- [AdSense Help Center](https://support.google.com/adsense)
- [AdSense Program Policies](https://support.google.com/adsense/answer/48182)
- [AdSense Optimization Guide](https://support.google.com/adsense/answer/6167117)

## Technical Details

### Files Modified

- `inc/adsense-settings.php` - Admin settings panel
- `inc/adsense-display.php` - Ad display functions
- `functions.php` - Module loading
- `template-parts/header.php` - Header ad hook
- `template-parts/footer.php` - Footer ad hook
- `template-parts/category-menu.php` - Sidebar ad hook
- `index.php` - Content ad hook

### Hooks Available

```php
// Ad placement hooks
do_action('thrivingstudio_after_header');
do_action('thrivingstudio_before_content');
do_action('thrivingstudio_sidebar_top');
do_action('thrivingstudio_before_footer');

// Utility functions
thrivingstudio_is_adsense_enabled();
thrivingstudio_should_show_ads();
thrivingstudio_get_adsense_options();
thrivingstudio_display_adsense_ad($ad_unit_id, $format, $attributes);
```

### Database Options

All AdSense settings are stored in the `thrivingstudio_adsense_options` option in the WordPress options table.

---

**Note**: This AdSense integration is designed to be compliant with Google AdSense policies and optimized for performance. Always follow Google's guidelines when implementing ads on your website. 
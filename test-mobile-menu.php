<?php
/**
 * Test page for mobile menu functionality
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4">Mobile Menu Test</h1>
    <p class="mb-4">This page is for testing the mobile menu button functionality.</p>
    
    <div class="bg-gray-100 p-4 rounded-lg">
        <h2 class="text-xl font-semibold mb-2">Test Instructions:</h2>
        <ol class="list-decimal list-inside space-y-1">
            <li>Open this page on a mobile device or resize your browser to mobile width</li>
            <li>Look for the hamburger menu button in the top-right corner</li>
            <li>Try tapping/clicking the button</li>
            <li>Check the browser console for debug messages</li>
        </ol>
    </div>
    
    <div class="mt-8 bg-blue-100 p-4 rounded-lg">
        <h2 class="text-xl font-semibold mb-2">Debug Information:</h2>
        <p>Open your browser's developer tools and check the console for debug messages.</p>
        <p>You should see messages like:</p>
        <ul class="list-disc list-inside space-y-1 text-sm">
            <li>"DOM Content Loaded - Setting up mobile menu"</li>
            <li>"Mobile menu button found: [object HTMLButtonElement]"</li>
            <li>"Mobile menu found: [object HTMLDivElement]"</li>
            <li>"Adding click event listener to mobile menu button"</li>
        </ul>
    </div>
</div>

<?php get_footer(); ?>

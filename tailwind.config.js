/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./**/*.php",
    "./frontend/**/*.js",
    "./frontend/index.css",
    // Scan all PHP files to ensure all Tailwind classes are included
  ],
  safelist: [
    // CRITICAL: Use patterns to include ALL utility classes
    {
      pattern: /^(container|mx-auto|mx-\d+|px-\d+|py-\d+|pt-\d+|pb-\d+|mb-\d+|mt-\d+)$/,
    },
    {
      pattern: /^(flex|flex-1|flex-col|flex-row|items-center|justify-center|justify-between|block|hidden)$/,
    },
    {
      pattern: /^(bg-white|bg-gray-\d+|bg-black|text-black|text-white|text-gray-\d+)$/,
    },
    {
      pattern: /^(text-sm|text-base|text-lg|text-xl|text-2xl|text-4xl|text-5xl|text-6xl)$/,
    },
    {
      pattern: /^(font-medium|font-semibold|font-bold|font-extrabold)$/,
    },
    {
      pattern: /^(rounded-lg|rounded-md|shadow|transition|duration-\d+)$/,
    },
    {
      pattern: /^(hover:bg-gray-\d+|hover:text-\w+|focus:outline-none|focus:ring-\d+)$/,
    },
    {
      pattern: /^(sm:|md:|lg:|xl:).+/,
    },
    {
      pattern: /^(dark:).+/,
    },
    // Specific classes that must be included
    'container', 'mx-auto', 'flex', 'flex-1', 'bg-white', 'text-black', 'px-4', 'items-center',
  ],
  theme: {
    extend: {
      // Optimize for performance
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
  // Performance optimizations
  corePlugins: {
    // Disable unused plugins to reduce CSS size
    preflight: true,
    container: true,
    accessibility: false, // Disable if not using
  },
}
module.exports = {
  content: [
    "./pages/*.{html,js}",
    "./index.html",
    "./js/*.js",
    "./components/*.html"
  ],
  theme: {
    extend: {
      colors: {
        // Primary Colors - Deep Market Green
        primary: {
          DEFAULT: "#2D5016", // deep-market-green
          50: "#F0F4ED", // light-market-green
          100: "#D9E5CC", // pale-market-green
          200: "#B8CFA0", // soft-market-green
          300: "#96B973", // medium-market-green
          400: "#75A347", // fresh-market-green
          500: "#538D1A", // vibrant-market-green
          600: "#2D5016", // deep-market-green
          700: "#244012", // darker-market-green
          800: "#1B300E", // darkest-market-green
          900: "#12200A", // deepest-market-green
        },
        
        // Secondary Colors - Warm Market Brown
        secondary: {
          DEFAULT: "#8B4513", // warm-market-brown
          50: "#F5F0EB", // light-market-brown
          100: "#E8D7C7", // pale-market-brown
          200: "#D4B89F", // soft-market-brown
          300: "#C09977", // medium-market-brown
          400: "#AC7A4F", // fresh-market-brown
          500: "#985B27", // vibrant-market-brown
          600: "#8B4513", // warm-market-brown
          700: "#6F370F", // darker-market-brown
          800: "#53290B", // darkest-market-brown
          900: "#371B07", // deepest-market-brown
        },
        
        // Accent Colors - Ripe Papaya Orange
        accent: {
          DEFAULT: "#FF6B35", // ripe-papaya-orange
          50: "#FFF4F0", // light-papaya-orange
          100: "#FFE0D1", // pale-papaya-orange
          200: "#FFBFA3", // soft-papaya-orange
          300: "#FF9E75", // medium-papaya-orange
          400: "#FF7D47", // fresh-papaya-orange
          500: "#FF6B35", // ripe-papaya-orange
          600: "#E55A2B", // vibrant-papaya-orange
          700: "#CC4921", // darker-papaya-orange
          800: "#B23817", // darkest-papaya-orange
          900: "#99270D", // deepest-papaya-orange
        },
        
        // Background Colors
        background: "#FEFEFE", // clean-market-table
        surface: {
          DEFAULT: "#F8F9FA", // subtle-vendor-stall
          50: "#FFFFFF", // pure-white
          100: "#F8F9FA", // subtle-vendor-stall
          200: "#E9ECEF", // light-gray
          300: "#DEE2E6", // medium-gray
          400: "#CED4DA", // soft-gray
          500: "#ADB5BD", // neutral-gray
        },
        
        // Text Colors
        text: {
          primary: "#1A1A1A", // clear-price-reading
          secondary: "#6C757D", // supporting-information
          muted: "#868E96", // muted-text
          light: "#ADB5BD", // light-text
        },
        
        // Status Colors
        success: {
          DEFAULT: "#28A745", // fresh-vegetable-confirmation
          50: "#F0F8F2", // light-success
          100: "#D4EDDA", // pale-success
          200: "#A3D9B1", // soft-success
          300: "#72C588", // medium-success
          400: "#41B15F", // fresh-success
          500: "#28A745", // fresh-vegetable-confirmation
          600: "#1E7E34", // darker-success
          700: "#155724", // darkest-success
        },
        
        warning: {
          DEFAULT: "#FFC107", // ripe-banana-caution
          50: "#FFFBF0", // light-warning
          100: "#FFF3CD", // pale-warning
          200: "#FFE69C", // soft-warning
          300: "#FFD86B", // medium-warning
          400: "#FFCA3A", // fresh-warning
          500: "#FFC107", // ripe-banana-caution
          600: "#E0A800", // darker-warning
          700: "#B08900", // darkest-warning
        },
        
        error: {
          DEFAULT: "#DC3545", // overripe-tomato-concern
          50: "#FDF2F2", // light-error
          100: "#F8D7DA", // pale-error
          200: "#F1AEB5", // soft-error
          300: "#EA868F", // medium-error
          400: "#E35D6A", // fresh-error
          500: "#DC3545", // overripe-tomato-concern
          600: "#C82333", // darker-error
          700: "#A71E2A", // darkest-error
        },
      },
      
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
        accent: ['Merriweather', 'serif'],
        merriweather: ['Merriweather', 'serif'],
      },
      
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1' }],
        '6xl': ['3.75rem', { lineHeight: '1' }],
      },
      
      boxShadow: {
        'minimal': '0 2px 8px rgba(0, 0, 0, 0.1)',
        'card': '0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24)',
        'elevated': '0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06)',
      },
      
      borderRadius: {
        'none': '0',
        'sm': '0.125rem',
        DEFAULT: '0.25rem',
        'md': '0.375rem',
        'lg': '0.5rem',
        'xl': '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
        'full': '9999px',
      },
      
      transitionDuration: {
        '200': '200ms',
        '250': '250ms',
        '300': '300ms',
      },
      
      transitionTimingFunction: {
        'ease-out': 'cubic-bezier(0, 0, 0.2, 1)',
      },
      
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },
      
      maxWidth: {
        '8xl': '88rem',
        '9xl': '96rem',
      },
      
      animation: {
        'fade-in': 'fadeIn 200ms ease-out',
        'slide-up': 'slideUp 250ms ease-out',
        'slide-down': 'slideDown 250ms ease-out',
      },
      
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
    },
  },
  plugins: [],
}
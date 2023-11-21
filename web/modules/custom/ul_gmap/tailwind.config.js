/* ./tailwind.config.js */
module.exports = {
    important: true,
    content: ["./**/*.{twig,php,html}"],
    safelist: [
      { pattern: /bg-/, },
      { pattern: /text-/, },
      { pattern: /m-/, },
      { pattern: /mt-/, },
      { pattern: /mb-/, },
      { pattern: /p-/, },
      { pattern: /pt-/, },
      { pattern: /pb-/, },
      { pattern: /border-/, },
    ],
    theme: {
      colors: {
        // Grayscale
        'black': "var(--black)",
        'white': "var(--white)",
        
        // Solid Colors
        'brand-red': 'var(--brand-ul-red)',
  
      },
      screens: {
  
        // Tablet
        'sm': '768px',
        // => @media (min-width: 768px) { ... }
  
        // Desktop
        'md': '1440px',
        // => @media (min-width: 1440px) { ... }
  
        // Super Desktop
        'lg': '1600px',
        // => @media (min-width: 1600px) { ... }
      },
      container: {
        center: true,
        screens: {
            'sm': "100%",
            'md': "100%",
            'lg': "1440px",
            'xl': "1800px",
        }
      },
      fontFamily: {
        sans: ['Open Sans', 'sans-serif'],
        // serif: ['Raleway', 'sans-serif'],
      },
      fontSize: {
        '-3': ['10px', {
            // letterSpacing: '',
            lineHeight: '14px',
        }],
        '-2': ['12px', {
            lineHeight: '16px',
        }],
        '-1': ['14px', {
            lineHeight: '20px',
        }],
        '0': ['16px', {
            lineHeight: '22px',
        }],
        '1': ['18px', {
            lineHeight: '24px',
        }],
        '2': ['20px', {
            lineHeight: '28px',
        }],
        '3': ['22px', {
            lineHeight: '30px',
        }],
        '4': ['24px', {
            lineHeight: '32px',
        }],
        '5': ['28px', {
            lineHeight: '36px',
        }],
        '6': ['32px', {
            lineHeight: '40px',
        }],
        '7': ['36px', {
            lineHeight: '44px',
        }],
        '8': ['40px', {
            lineHeight: '48px',
        }],
        '9': ['44px', {
            lineHeight: '52px',
        }],
        '10': ['48px', {
            lineHeight: '56px',
        }],
        '11': ['56px', {
            lineHeight: '64px',
        }],
        '12': ['64px', {
            lineHeight: '72px',
        }],
        '13': ['72px', {
            lineHeight: '80px',
        }],
        '14': ['88px', {
            lineHeight: '96px',
        }],
        '15': ['104px', {
            lineHeight: '116px',
        }],
        '16': ['120px', {
            lineHeight: '132px',
        }],
        '17': ['156px', {
            lineHeight: '172px',
        }],
  
      },
      opacity: {
        '0': '0',
        '25': '.25',
        '50': '.5',
        '75': '.75',
        '10': '.1',
        '20': '.2',
        '30': '.3',
        '40': '.4',
        '50': '.5',
        '60': '.6',
        '70': '.7',
        '80': '.8',
        '90': '.9',
        '100': '1',
      },
      spacing: {
        '0': '0px',
        '1px': '1px',
        '2px': '2px',
        '4px': '4px',
        '8px': '8px',
        '12px': '12px',
        '16px': '16px',
        '20px': '20px',
        '24px': '24px',
        '32px': '32px',
        '36px': '36px',
        '40px': '40px',
        '44px': '44px',
        '48px': '48px',
        '52px': '52px',
        '56px': '56px',
        '64px': '64px',
        '72px': '72px',
        '88px': '88px',
        '100px': '100px',
        '120px': '120px',
        '140px': '140px',
        '160px': '160px',
        '200px': '200px',
      },
      extend: {},
    },
    variants: {},
    plugins: [],  
  }
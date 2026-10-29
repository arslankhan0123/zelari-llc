<?php

/*
 * Calculator tools shown at /calculators.
 * Each entry: name, icon (font-awesome), color, cat (financial|health|math|datetime|misc), desc.
 * 'live' => true makes it usable; without it the card shows COMING SOON.
 * Admin can override per-tool state and hide the whole section (see App\Support\Calculators).
 */
return [
    'scientific-calculator' => ['name' => 'Scientific Calculator', 'icon' => 'fa-calculator',        'color' => '#6d28d9', 'cat' => 'math',      'live' => true, 'desc' => 'Full keypad calculator with scientific functions — sin, cos, log, powers, roots and more.'],
    'percentage-calculator' => ['name' => 'Percentage Calculator', 'icon' => 'fa-percent',           'color' => '#6d28d9', 'cat' => 'math',      'live' => true, 'desc' => 'Find X% of a number, what percent one number is of another, and percentage change.'],
    'average-calculator'    => ['name' => 'Average Calculator',    'icon' => 'fa-chart-simple',      'color' => '#6d28d9', 'cat' => 'math',      'live' => true, 'desc' => 'Mean, median, min, max, sum and standard deviation of any list of numbers.'],
    'gpa-calculator'        => ['name' => 'GPA Calculator',        'icon' => 'fa-graduation-cap',    'color' => '#6d28d9', 'cat' => 'math',      'live' => true, 'desc' => 'Calculate your semester GPA from course credits and letter grades.'],

    'loan-calculator'       => ['name' => 'Loan Calculator',       'icon' => 'fa-hand-holding-dollar', 'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Monthly payment (EMI), total payment and total interest for any loan.'],
    'mortgage-calculator'   => ['name' => 'Mortgage Calculator',   'icon' => 'fa-house-chimney',     'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Estimate your monthly home payment with down payment, term and interest rate.'],
    'compound-interest'     => ['name' => 'Compound Interest',     'icon' => 'fa-arrow-trend-up',    'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Watch your savings grow with compounding — monthly, quarterly or yearly.'],
    'simple-interest'       => ['name' => 'Simple Interest',       'icon' => 'fa-coins',             'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Quick simple-interest and total amount for a principal, rate and time.'],
    'investment-calculator' => ['name' => 'Investment Calculator', 'icon' => 'fa-sack-dollar',       'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Future value of an initial amount plus monthly contributions at a given return.'],
    'roi-calculator'        => ['name' => 'ROI Calculator',        'icon' => 'fa-chart-line',        'color' => '#1f7244', 'cat' => 'financial', 'live' => true, 'desc' => 'Return on investment — gain and ROI percentage from cost and final value.'],
    'discount-calculator'   => ['name' => 'Discount Calculator',   'icon' => 'fa-tags',              'color' => '#e5322d', 'cat' => 'financial', 'live' => true, 'desc' => 'Final price and how much you save after a percentage discount.'],
    'tip-calculator'        => ['name' => 'Tip Calculator',        'icon' => 'fa-receipt',           'color' => '#e5322d', 'cat' => 'financial', 'live' => true, 'desc' => 'Tip amount, total bill and per-person split in seconds.'],
    'sales-tax-calculator'  => ['name' => 'Sales Tax Calculator',  'icon' => 'fa-file-invoice-dollar', 'color' => '#e5322d', 'cat' => 'financial', 'live' => true, 'desc' => 'Add tax to a net price or extract the tax from a tax-inclusive price.'],
    'margin-calculator'     => ['name' => 'Profit Margin',         'icon' => 'fa-scale-balanced',    'color' => '#e5322d', 'cat' => 'financial', 'live' => true, 'desc' => 'Profit, margin % and markup % from your cost and selling price.'],

    'bmi-calculator'        => ['name' => 'BMI Calculator',        'icon' => 'fa-weight-scale',      'color' => '#0ea5e9', 'cat' => 'health',    'live' => true, 'desc' => 'Body Mass Index with category — metric or imperial units.'],
    'calorie-calculator'    => ['name' => 'Calorie Calculator',    'icon' => 'fa-fire-flame-curved', 'color' => '#0ea5e9', 'cat' => 'health',    'live' => true, 'desc' => 'Daily calories to maintain, lose or gain weight (Mifflin-St Jeor BMR).'],
    'ideal-weight'          => ['name' => 'Ideal Weight',          'icon' => 'fa-person',            'color' => '#0ea5e9', 'cat' => 'health',    'live' => true, 'desc' => 'Healthy weight range for your height using four medical formulas.'],

    'age-calculator'        => ['name' => 'Age Calculator',        'icon' => 'fa-cake-candles',      'color' => '#f2b705', 'cat' => 'datetime',  'live' => true, 'desc' => 'Exact age in years, months and days — plus a countdown to the next birthday.'],
    'date-calculator'       => ['name' => 'Date Calculator',       'icon' => 'fa-calendar-days',     'color' => '#f2b705', 'cat' => 'datetime',  'live' => true, 'desc' => 'Days between two dates, or add and subtract days, weeks and months from a date.'],

    'unit-converter'        => ['name' => 'Unit Converter',        'icon' => 'fa-right-left',        'color' => '#8b5cf6', 'cat' => 'misc',      'live' => true, 'desc' => 'Convert length, weight, temperature, area, volume, speed and data units.'],
];

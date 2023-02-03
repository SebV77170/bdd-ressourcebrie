<?php

    /**
     * Convert Currency String to Decimal
     * 
     * Attempts to support international currency formats by first converting them
     * to a standardized format PHP can process consistently. 
     * 
     * First, per the SI/ISO 31-0 standard, there are several delimiting options
     * for the integer portion (before the radix) that allow for easier reading. We need
     * to convert this portion to an integer without readability delimiters.
     * 
     * Second, we need to convert the radix (decimal separator) to a "point" or 
     * "period" symbol.
     * 
     * Finally, we strip unwanted characters and convert to float.
     * 
     * @see https://en.wikipedia.org/wiki/Decimal_separator
     * @see https://en.wikipedia.org/wiki/ISO_31-0
     * @see https://docs.oracle.com/cd/E19455-01/806-0169/overview-9/index.html
     */
    function currencyToDecimal($value) {

        // Ensure string does not have leading/trailing spaces
        $value = trim($value);

        /**
         * Standardize readability delimiters
         *****************************************************/

            // Space used as thousands separator between digits
            $value = preg_replace('/(\d)(\s)(\d)/', '$1$3', $value); 

            // Decimal used as delimiter when comma used as radix
            if (stristr($value, '.') && stristr($value, ',')) {
                // Ensure last period is BEFORE first comma
                if (strrpos($value, '.') < strpos($value, ',')) {
                    $value = str_replace('.', '', $value);
                }
            }

            // Comma used as delimiter when decimal used as radix
            if (stristr($value, ',') && stristr($value, '.')) {
                // Ensure last period is BEFORE first comma
                if (strrpos($value, ',') < strpos($value, '.')) {
                    $value = str_replace(',', '', $value);
                }
            }

        /**
         * Standardize radix (decimal separator)
         *****************************************************/

            // Possible radix options
            $radixOptions = [',', ' '];

            // Convert comma radix to "point" or "period"
            $value = str_replace(',', '.', $value);

        /**
         * Strip non-numeric and non-radix characters
         *****************************************************/

            // Remove other symbols like currency characters
            $value = preg_replace('/[^\d\.]/', '', $value);

        /**
         * Convert to float value
         *****************************************************/

            // String to float first before formatting
            $value = floatval($value);

        /**
         * Give final value 
         *****************************************************/

            return $value;

    }
    
    ?>
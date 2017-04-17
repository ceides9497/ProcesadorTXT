<?php
include 'verhoeff.php';
include 'alleged-RC4.php';
include 'base64.php';

class ControlCode {

    /**
     * @param String $authorizationNumber Numero de autorizacion
     * @param String $invoiceNumber Numero de factura
     * @param String $nitci Número de Identificación Tributaria o Carnet de Identidad
     * @param String $dateOfTransaction fecha de transaccion de la forma AAAAMMDD
     * @param String $transactionAmount Monto de la transacción
     * @param String $dosageKey Llave de dosificación
     */
    function generate($authorizationNumber, $invoiceNumber, $nitci,
                      $dateOfTransaction, $transactionAmount, $dosageKey){

        //validación de datos
        if( empty($authorizationNumber) || empty($invoiceNumber) || empty($dateOfTransaction) ||
                empty($transactionAmount) || empty($dosageKey) || (!strlen($nitci)>0 )  ){
            throw new InvalidArgumentException('<b>Todos los campos son obligatorios</b>');
        }else{
            $this->validateNumber($authorizationNumber);
            $this->validateNumber($invoiceNumber);
            $this->validateNumber($dateOfTransaction);
            $this->validateNumber($nitci);
            $this->validateNumber($transactionAmount);
            $this->validateDosageKey($dosageKey);
        }

        //redondea monto de transaccion
        $transactionAmount = $this->roundUp($transactionAmount);

        /* ========== PASO 1 ============= */
        $invoiceNumber = self::addVerhoeffDigit($invoiceNumber,2);
        $nitci = self::addVerhoeffDigit($nitci,2);
        $dateOfTransaction = self::addVerhoeffDigit($dateOfTransaction,2);
        $transactionAmount = self::addVerhoeffDigit($transactionAmount,2);
        //se suman todos los valores obtenidos
        $sumOfVariables = $invoiceNumber
                          + $nitci
                          + $dateOfTransaction
                          + $transactionAmount;
        //A la suma total se añade 5 digitos Verhoeff
        $sumOfVariables5Verhoeff = self::addVerhoeffDigit($sumOfVariables,5);

         /* ========== PASO 2 ============= */
        $fiveDigitsVerhoeff = substr($sumOfVariables5Verhoeff,strlen($sumOfVariables5Verhoeff)-5);
        $numbers = str_split($fiveDigitsVerhoeff);
        for($i=0;$i<5;$i++){
             $numbers[$i] = $numbers[$i] + 1;
        }

        $string1 = substr($dosageKey,0, $numbers[0] );
        $string2 = substr($dosageKey,$numbers[0], $numbers[1] );
        $string3 = substr($dosageKey,$numbers[0]+ $numbers[1], $numbers[2] );
        $string4 = substr($dosageKey,$numbers[0]+ $numbers[1]+ $numbers[2], $numbers[3] );
        $string5 = substr($dosageKey,$numbers[0]+ $numbers[1]+ $numbers[2]+ $numbers[3], $numbers[4] );

        $authorizationNumberDKey = $authorizationNumber . $string1;
        $invoiceNumberdKey = $invoiceNumber . $string2;
        $NITCIDKey = $nitci . $string3;
        $dateOfTransactionDKey = $dateOfTransaction . $string4;
        $transactionAmountDKey = $transactionAmount . $string5;

          /* ========== PASO 3 ============= */
        //se concatena cadenas de paso 2
        $stringDKey = $authorizationNumberDKey . $invoiceNumberdKey . $NITCIDKey . $dateOfTransactionDKey . $transactionAmountDKey;
        //Llave para cifrado + 5 digitos Verhoeff generado en paso 2
        $keyForEncryption = $dosageKey . $fiveDigitsVerhoeff;
        //se aplica AllegedRC4
        $allegedRC4String = AllegedRC4::encryptMessageRC4($stringDKey, $keyForEncryption,true);

        /* ========== PASO 4 ============= */
        //cadena encriptada en paso 3 se convierte a un Array
        $chars = str_split($allegedRC4String);
        //se suman valores ascii
        $totalAmount=0;
        $sp1=0;
        $sp2=0;
        $sp3=0;
        $sp4=0;
        $sp5=0;

        $tmp=1;
        for($i=0; $i<strlen($allegedRC4String);$i++){
            $totalAmount += ord($chars[$i]);
            switch($tmp){
                case 1: $sp1 += ord($chars[$i]); break;
                case 2: $sp2 += ord($chars[$i]); break;
                case 3: $sp3 += ord($chars[$i]); break;
                case 4: $sp4 += ord($chars[$i]); break;
                case 5: $sp5 += ord($chars[$i]); break;
            }
            $tmp = ($tmp<5)?$tmp+1:1;
        }

        /* ========== PASO 5 ============= */
        //suma total * sumas parciales dividido entre resultados obtenidos
        //entre el dígito Verhoeff correspondiente más 1 (paso 2)
        $tmp1 = floor($totalAmount*$sp1/$numbers[0]);
        $tmp2 = floor($totalAmount*$sp2/$numbers[1]);
        $tmp3 = floor($totalAmount*$sp3/$numbers[2]);
        $tmp4 = floor($totalAmount*$sp4/$numbers[3]);
        $tmp5 = floor($totalAmount*$sp5/$numbers[4]);
        //se suman todos los resultados
        $sumProduct = $tmp1 + $tmp2 + $tmp3 + $tmp4 + $tmp5;
        //se obtiene base64
        $base64SIN = Base64SIN::convert($sumProduct);

        /* ========== PASO 6 ============= */
        //Aplicar el AllegedRC4 a la anterior expresión obtenida
        return AllegedRC4::encryptMessageRC4($base64SIN, $dosageKey.$fiveDigitsVerhoeff);
    }

    /**
     * Añade N digitos Verhoeff a una cadena de texto
     * @param value String
     * @param max numero de digitos a agregar
     * @return String cadena original + N digitos Verhoeff
     */
    static function addVerhoeffDigit($value,$max){
      $value = self::exp_to_dec($value);
       for($i=1;$i<=$max;$i++){
           $value .= Verhoeff::calc($value);
       }
       return $value;
    }

     /**
     * Redondea hacia arriba
     * @param value cadena con valor numerico de la forma 123 123.4 123,4
     */
    function roundUp($value){
        //reemplaza (,) por (.)
        $value2 = str_replace(',','.',$value);
        //redondea a 0 decimales
        return round($value2, 0, PHP_ROUND_HALF_UP);
    }

    function validateNumber($value){
        if(!preg_match('/^[0-9,.]+$/', $value)){
            throw new InvalidArgumentException(sprintf("Error! Valor restringido a número, %s no es un número.",$value));
        }
    }

    function validateDosageKey($value){
        if(!preg_match('/^[A-Za-z0-9=#()*+-_\@\[\]{}%$]+$/', $value)){
            throw new InvalidArgumentException(sprintf("Error! llave de dosificación,<b> %s </b>contiene caracteres NO permitidos.",$value));
        }
    }

    static function exp_to_dec($float_str)
   // formats a floating point number string in decimal notation, supports signed floats, also supports non-standard formatting e.g. 0.2e+2 for 20
   // e.g. '1.6E+6' to '1600000', '-4.566e-12' to '-0.000000000004566', '+34e+10' to '340000000000'
   // Author: Bob
   {
       // make sure its a standard php float string (i.e. change 0.2e+2 to 20)
       // php will automatically format floats decimally if they are within a certain range
       $float_str = (string)((float)($float_str));

       // if there is an E in the float string
       if(($pos = strpos(strtolower($float_str), 'e')) !== false)
       {
           // get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
           $exp = substr($float_str, $pos+1);
           $num = substr($float_str, 0, $pos);

           // strip off num sign, if there is one, and leave it off if its + (not required)
           if((($num_sign = $num[0]) === '+') || ($num_sign === '-')) $num = substr($num, 1);
           else $num_sign = '';
           if($num_sign === '+') $num_sign = '';

           // strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
           if((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) $exp = substr($exp, 1);
           else trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);

           // get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
           $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos+1));
           // get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
           $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));

           // work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
           if($exp_sign === '+') $num_zeros = $exp - $right_dec_places;
           else $num_zeros = $exp - $left_dec_places;

           // build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
           $zeros = str_pad('', $num_zeros, '0');

           // strip decimal from num, e.g. 1.6 => 16
           if($dec_pos !== false) $num = str_replace('.', '', $num);

           // if positive exponent, return like 1600000
           if($exp_sign === '+') return $num_sign.$num.$zeros;
           // if negative exponent, return like 0.0000016
           else return $num_sign.'0.'.$zeros.$num;
       }
       // otherwise, assume already in decimal notation and return
       else return $float_str;
   }

}//end:class

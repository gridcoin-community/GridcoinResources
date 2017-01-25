<?php

/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org>
*/

/**
 *  Verifies a supplied address
 *  @source http://stackoverflow.com/a/21560021
 *  @requirements PHP BC-Math
 *  @version 1.0
 *   
 *  @param address string to test
 *  @param testnet if testnet address space should be included
 *  @return true if valid
 */
function checkGRCAddress($address, $testnet=false)
{
    $origbase58 = $address;
    $dec = "0";

    for ($i = 0; $i < strlen($address); $i++)
    {
       $dec = bcadd(bcmul($dec,"58",0),strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz",substr($address,$i,1)),0);
    }

    $address = "";

    while (bccomp($dec,0) == 1)
    {
        $dv = bcdiv($dec,"16",0);
        $rem = (integer)bcmod($dec,"16");
        $dec = $dv;
        $address = $address.substr("0123456789ABCDEF",$rem,1);
    }

    $address = strrev($address);

    if (strlen($address) != 50) { return false; }

    /* Gridcoin Research addresses start with 
     *  R - 62, Pubkey
     *  G - 37, Pubkey Classic
     *  b - 85, Script
     */
    if (hexdec(substr($address,0,2)) != 37 
        XOR hexdec(substr($address,0,2)) != 62
        XOR hexdec(substr($address,0,2)) != 85
        XOR ($testnet && hexdec(substr($address,0,2)) != 111)
        XOR ($testnet && hexdec(substr($address,0,2)) != 196))
    { 
        return false; 
    }

    return substr(strtoupper(hash("sha256",hash("sha256",pack("H*",substr($address,0,strlen($address)-8)),true))),0,8) == substr($address,strlen($address)-8);
}

$result = checkGRCAddress("MyAddress");
if($result) { echo "Address Verified"; } 
else { echo "Address Invalid!"; }

?>

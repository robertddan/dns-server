<?php

$rSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

if ($rSocket === false) print_r(socket_strerror(socket_last_error())).PHP_EOL;
if (!socket_bind($rSocket, "0.0.0.0", 53)) { 
	socket_close($rSocket);
	print_r(socket_strerror(socket_last_error())).PHP_EOL;
}
socket_recvfrom($rSocket, $buf, 65535, 0, $clientIP, $clientPort);

#$stz = bin2hex($buf);
#var_dump($stz);
#die();

#socket_connect($rSocket, '8.8.8.8', 53);
#socket_send($rSocket, $sMessage, strlen($msg), 0);


#$sMessage = "ed 4f 01 20 00 01 00 00 00 00 00 01 06 69 6d 61 67 65 73 06 67 6f 6f 67 6c 65 03 63 6f 6d 00 00 01 00 01";
$sMessage = "ed4f0120000100000000000106696d6167657306676f6f676c6503636f6d0000010001000029100000000000000c000a00088ff1b12cbe8f026b";


$aBuffer = array_map(function($sField) {
	$sField = base_convert(ord($sField), 10, 2);
	$sField = str_pad($sField, 8, 0, STR_PAD_LEFT);
	return $sField;
}, str_split($buf));

$aHexBuffer = array_map(function($sField) {
	$sField = base_convert(ord($sField), 10, 16);
	$sField = str_pad($sField, 2, 0, STR_PAD_LEFT);
	return $sField;
}, str_split($buf));

$aHexString = array_map(function($sField) {
	$sField = base_convert($sField, 16, 2);
	$sField = str_pad($sField, 2, 0, STR_PAD_LEFT);
	return $sField;
}, str_split($sMessage));


var_dump(['aBuffer', implode(" ", $aBuffer)]);

$aMessage = array(
	'HEADER' => array(
		'ID' => array(),
		'QR' => array(),
		'Opcode' => array(),
		'AA' => array(),
		'TC' => array(),
		'RD' => array(),
		'RA' => array(),
		'Z' => array(),
		'RCODE' => array(),
		'QDCOUNT' => array(),
		'ANCOUNT' => array(),
		'NSCOUNT' => array(),
		'ARCOUNT' => array(),
	),
	'QUESTION' => array(
		'QNAME' => array(),
		'QTYPE' => array(),
		'QCLASS' => array(),
	),
	'ANSWER' => array(
		'NAME' => array(),
		'TYPE' => array(),
		'CLASS' => array(),
		'TTL' => array(),
		'RDLENGTH' => array(),
		'RDATA' => array()
	)
);


/*
																	1  1  1  1  1  1
		0  1  2  3  4  5  6  7  8  9  0  1  2  3  4  5
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                      ID                       |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|QR|   Opcode  |AA|TC|RD|RA|   Z    |   RCODE   |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                    QDCOUNT                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                    ANCOUNT                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                    NSCOUNT                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                    ARCOUNT                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
*/
$aHeader = array();
foreach($aBuffer as $k => $sField)
{
	switch($k){
		case 0: # tx id
		case 1:
			array_push($aMessage['HEADER']['ID'], base_convert($sField, 2, 16));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 2: #1 QR, 4 Opcode, 1 AA, 1 TC, 1 RD
			$aFields = str_split($sField);
			array_push($aMessage['HEADER']['QR'], base_convert($sField[0], 2, 16));
			array_push($aMessage['HEADER']['Opcode'], ...array_map(function($sMapField) { return base_convert($sMapField, 2, 10); }, array_slice($aFields, 1, 4)) );
			array_push($aMessage['HEADER']['AA'], base_convert($aFields[5], 2, 10));
			array_push($aMessage['HEADER']['TC'], base_convert($aFields[6], 2, 10));
			array_push($aMessage['HEADER']['RD'], base_convert($aFields[7], 2, 10));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 3: # 1 RA, 3 Z, 4 RCODE
			$aFields = str_split($sField);
			array_push($aMessage['HEADER']['RA'], base_convert($aFields[1], 2, 10));
			array_push($aMessage['HEADER']['Z'], ...array_map(function($sMapField) { return base_convert($sMapField, 2, 10); }, array_slice($aFields, 1, 3)) );
			array_push($aMessage['HEADER']['RCODE'], ...array_map(function($sMapField) { return base_convert($sMapField, 2, 10); }, array_slice($aFields, 4, 4)) );
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 4: # QDCOUNT
		case 5:
			array_push($aMessage['HEADER']['QDCOUNT'], base_convert($sField, 2, 16));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 6: # ANCOUNT
		case 7:
			array_push($aMessage['HEADER']['ANCOUNT'], base_convert($sField, 2, 16));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 8: # NSCOUNT
		case 9:
			array_push($aMessage['HEADER']['NSCOUNT'], base_convert($sField, 2, 16));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
		case 10: # ARCOUNT
		case 11:
			array_push($aMessage['HEADER']['ARCOUNT'], base_convert($sField, 2, 16));
			array_push($aHeader, $sField);
			var_dump(implode(" ", [$k, '$aHeader', $sField]));
		break;
	}
}

/*
																	1  1  1  1  1  1
		0  1  2  3  4  5  6  7  8  9  0  1  2  3  4  5
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                                               |
	/                     QNAME                     /
	/                                               /
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                     QTYPE                     |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                     QCLASS                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
*/
$ikCount = $ikLength = $iCount = $k_qtype = $k_qclass = 0;
$ik_label = -1;
$aQuestion = array();
foreach($aBuffer as $k => $sField)
{
	if ($k < 12) continue;
	switch($k){
		case (bccomp($k, bcadd(12, $ikCount, 0), 0) === 0): # domain length
			$iCount = (int) base_convert($sField, 2, 10);
			if ($iCount == 0) { $k_qtype = $ikCount + 1; $ikLength = count($aBuffer) + 1; }
			else $ikLength = $ikCount + 1;
			$ik_label = $ik_label + 1;
			if (!isset($aMessage['QUESTION']['QNAME'][$ik_label])) $aMessage['QUESTION']['QNAME'][$ik_label] = array();
			array_push($aMessage['QUESTION']['QNAME'][$ik_label], base_convert($sField, 2, 10));
			array_push($aQuestion, $sField);
			var_dump(implode(" ", [$k, '$aQuestion 1', $sField]));
		break;
		case (bccomp($k, bcadd(12, $ikLength, 0), 0) === 0): # QNAME	
			$iCount = $iCount - 1;
			$ikLength = $ikLength + 1;
			if ($iCount == 0) $ikCount = $ikLength;
			array_push($aMessage['QUESTION']['QNAME'][$ik_label], chr(base_convert($sField, 2, 10)));
			array_push($aQuestion, $sField);
			var_dump(implode(" ", [$k, '$aQuestion 2', $sField]));
		break;
		case ((bccomp($k, bcadd(12, $k_qtype, 0), 0) === 0) || (bccomp($k, bcadd(12, bcadd(1, $k_qtype, 0), 0), 0) === 0)): # QTYPE
			$k_qclass = $k_qtype + 2;
			array_push($aMessage['QUESTION']['QTYPE'], base_convert($sField, 2, 16));
			array_push($aQuestion, $sField);
			var_dump(implode(" ", [$k, '$aQuestion 3333', '$sField', $sField, 'k_pe', $k_qtype, ]));
		break;
		case ((bccomp($k, bcadd(12, $k_qclass, 0), 0) === 0) || (bccomp($k, bcadd(12, bcadd(1, $k_qclass, 0), 0), 0) === 0)): # QCLASS
			array_push($aMessage['QUESTION']['QCLASS'], base_convert($sField, 2, 16));
			$k_answer = $k_qclass + 1;
			array_push($aQuestion, $sField);
			var_dump(implode(" ", [$k, '$aQuestion 4', $sField]));
		break;
	}
}


/*
																	1  1  1  1  1  1
		0  1  2  3  4  5  6  7  8  9  A  B  C  D  E  F
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                                               |
	/                                               /
	/                      NAME                     /
	|                                               |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                      TYPE                     |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                     CLASS                     |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                      TTL                      |
	|                                               |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
	|                   RDLENGTH                    |
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--|
	/                     RDATA                     /
	/                                               /
	+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+--+
*/
$aAnswer = array();
foreach($aBuffer as $k => $sField)
{
	if ($k < 12) continue;
	switch($k){
		case (($k == (12 + $k_answer + 1)) || ($k == (12 + $k_qclass + 2))): # NAME
			array_push($aMessage['ANSWER']['NAME'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
		case (($k == (12 + $k_qclass + 3)) || ($k == (12 + $k_qclass + 4))): # TYPE
			array_push($aMessage['ANSWER']['TYPE'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
		case (($k == (12 + $k_qclass + 5)) || ($k == (12 + $k_qclass + 6))): # CLASS
			array_push($aMessage['ANSWER']['CLASS'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
		case (($k == (12 + $k_qclass + 7)) || ($k == (12 + $k_qclass + 8))): # TTL
			array_push($aMessage['ANSWER']['TTL'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
		case (($k == (12 + $k_qclass + 9)) || ($k == (12 + $k_qclass + 10))): # RDLENGTH
			array_push($aMessage['ANSWER']['RDLENGTH'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
		case (($k == (12 + $k_qclass + 11)) || ($k == (12 + $k_qclass + 12))): # RDATA
			array_push($aMessage['ANSWER']['RDATA'], base_convert($sField, 2, 16));
			array_push($aAnswer, $sField);
			var_dump(implode(" ", [$k, '$aAnswer', $sField]));
		break;
	}
}



#var_dump(array('$aBuffer', $aBuffer));
#var_dump(implode(" ", $aBuffer));
#var_dump(implode(" ", array_map("hexdec", $aHexBuffer)));

#var_dump(implode(" ", array_map("hexdec", $aMessage)));
var_dump($aMessage);

var_dump(array(
	"aHeader" => implode(" ", $aHeader),
	"aQuestion" => implode(" ", $aQuestion),
	"aAnswer" => implode(" ", $aAnswer),
));


var_dump(implode(" ", $aHexBuffer));

/*


array(3) {
  ["aHeader"]=>
  string(107) "01100010 10100101 00000001 00100000 00000000 00000001 00000000 00000000 00000000 00000000 00000000 00000001"
  ["aQuestion"]=>
  string(242) "00000011 00000011 01110111 01110111 01110111 01110111 01110111 01110111 00001001 01110011 01110101 01101001 01110100 01100101 01111010 01101001 01100101 01101100 00000011 01100011 01101111 01101101 00000000 00000000 00000001 00000000 00000001"
  ["aAnswer"]=>
  string(98) "00000000 00000000 00101001 00010000 00000000 00000000 00000000 00000000 00000000 00000000 00001100"
}


string(521) "01100001 10010111 00000001 00100000 00000000 00000001 00000000 00000000 00000000 00000000 00000000 00000001 00000011 01110111 01110111 01110111 00001001 01110011 01110101 01101001 01110100 01100101 01111010 01101001 01100101 01101100 00000011 01100011 01101111 011011
01 00000000 00000000 00000001 00000000 00000001 00000000 00000000 00101001 00010000 00000000 00000000 00000000 00000000 00000000 00000000 00001100 00000000 00001010 00000000 00001000 01101011 00011011 00000010 00100110 11101101 11010000 00001100 00000010"

string(173) "19 0a 01 20 00 01 00 00 00 00 00 01 03 77 77 77 09 73 75 69 74 65 7a 69 65 6c 03 63 6f 6d 00 00 01 00 01 00 00 29 10 00 00 00 00 00 00 0c 00 0a 00 08 aa df 81 78 29 db c3 94"



00000000 00001010 00000000 00001000 01101011 00011011 00000010 00100110 11101101 11010000 00001100 00000010

00 0a 00 08 aa df 81 78 29 db c3 94



*/
	

#socket_send($socket, $ret, 667, 0);

?>


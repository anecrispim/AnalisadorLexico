<?php
print('<h3>Analisador Léxico</h3>');
// $sCadeia = 'x = 10; while (x > 0){ print(x) X = x - 1 } if (x == 0) print(0) for';
$sCadeia = 'function x (int a, int b) { x = a + b; if (x > 10){ print(x); } }';

$sCadeia = str_replace(' ', '', $sCadeia);
$aEntradas = str_split(strtolower($sCadeia));
$aEstados = [];
$aPosicao = [];
$aTokens = [];
$aLexema = [];
$sJson = file_get_contents('matrizTransicao2.json');
$aMatrizTransicao = json_decode($sJson, true);
// $aEstadosFinais = [
//       1 => 'ESPACO'
//     , 2 => 'SIMBOLO' 
//     , 3 => 'OPERADOR'
//     , 4 => 'NUMERO'
//     , 5 => 'OPERADORLOGICO'
//     , 6 => 'OPERADORLOGICO'
//     , 7 => 'VARIAVEL'
//     , 8 => 'VARIAVEL'
//     , 9 => 'VARIAVEL'
//     , 10 => 'VARIAVEL'
//     , 11 => 'VARIAVEL'
//     , 12 => 'VARIAVEL'
//     , 13 => 'VARIAVEL'
//     , 14 => 'IF'
//     , 15 => 'VARIAVEL'
//     , 16 => 'VARIAVEL'
//     , 17 => 'FOR'
//     , 18 => 'VARIAVEL'
//     , 19 => 'VARIAVEL'
//     , 20 => 'VARIAVEL'
//     , 21 => 'VARIAVEL'
//     , 22 => 'PRINT'
//     , 23 => 'WHILE'
// ];
$aEstadosFinais = [
    1 => 'AP'
  , 2 => 'FP' 
  , 3 => 'OPE'
  , 4 => 'VIRG'
  , 5 => 'OPE'
  , 6 => 'CONST'
  , 7 => 'PV'
  , 8 => 'OPELOG'
  , 9 => 'ATRIB'
  , 10 => 'ID'
  , 11 => 'ID'
  , 12 => 'ID'
  , 13 => 'ID'
  , 14 => 'ID'
  , 15 => 'ID'
  , 16 => 'AC'
  , 17 => 'FC'
  , 18 => 'ID'
  , 19 => 'IF'
  , 20 => 'ID'
  , 21 => 'ID'
  , 22 => 'ID'
  , 23 => 'ID'
  , 24 => 'INT'
  , 25 => 'ID'
  , 26 => 'ID'
  , 27 => 'ID'
  , 28 => 'ID'
  , 29 => 'ID'
  , 30 => 'ID'
  , 31 => 'PRINT'
  , 32 => 'WHILE'
  , 33 => 'ID'
  , 34 => 'ID'
  , 35 => 'FUNCTION'
];

// $aEstadosEspeciais = [
//       14 => 'IF'
//     , 17 => 'FOR'
//     , 22 => 'PRINT'
//     , 23 => 'WHILE'
// ];

$aEstadosEspeciais = [
    19 => 'IF'
  , 24 => 'INT'
  , 31 => 'PRINT'
  , 32 => 'WHILE'
  , 35 => 'FUNCTION'
];

// $aNaoConcatena = [
//     2 => 'SIMBOLO'
//   , 3 => 'OPERADOR'
// ];
$aNaoConcatena = [
    3 => 'OPE'
  , 5 => 'OPE'
  ,17 => 'FC'
];
$sLexema = '';
$sEstado = '';
$iEstado = 0;
// MONTA TABELA SIMULADOR LEXICO
for ($i = 0; $i < count($aEntradas); $i++) {
    $iEstado = $aMatrizTransicao[$iEstado][$aEntradas[$i]];
    if (!empty($aEstadosFinais[$iEstado])) {
        $aEstados[] = $iEstado;
        $aPosicao[] = $i;
        $aTokens[] = $aEstadosFinais[$iEstado];
        $aLexema[] = $aEntradas[$i];
        if (!empty($aEstadosEspeciais[$iEstado])) {
            for ($j=(strlen($aEstadosEspeciais[$iEstado])-1); $j >= 1; $j--) { 
                unset($aPosicao[$i - $j]);
                unset($aTokens[$i - $j]);
                $sLexema = sprintf('%s%s', $sLexema, $aLexema[$i - $j]);
                $sEstado = sprintf('%s,%s', $sEstado, $aEstados[$i - $j]);
                unset($aLexema[$i - $j]);
                unset($aEstados[$i - $j]);
            }
            $aTokens[$i] = $aEstadosEspeciais[$iEstado];
            $aLexema[$i] = sprintf('%s%s', $sLexema, $aLexema[$i]);
            $aEstados[$i] = str_replace(',,', ',', ltrim(sprintf('%s,%s', $sEstado, $aEstados[$i]), ','));
            $aPosicao[$i] = $i - (strlen($aEstadosEspeciais[$iEstado])-1);
            $sLexema = '';
            $sEstado = '';
        } else if ($i != 0 && !in_array($aTokens[$i], $aNaoConcatena)) {
            if ($aTokens[$i] == $aTokens[$i - 1]) {
                $aEstados[$i - 1] = str_replace(',,', ',', ltrim(sprintf(',%s,%s', $aEstados[$i - 1], $iEstado), ','));
                $aLexema[$i - 1] = sprintf('%s%s', $aLexema[$i - 1], $aEntradas[$i]);
                unset($aEstados[$i]);
                unset($aLexema[$i]);
                unset($aPosicao[$i]);
                unset($aTokens[$i]);
            }
        }                              
    } else {
        printf('Token não identificado para o lexema: %s', $aEntradas[$i]);
        break;
    }
}

$sTr = '<tr>%s%s%s%s</tr>';
$aTrs = [];
foreach ($aTokens as $i => $sVal) {
    $sTdToken = sprintf('<td>%s</td>', $aTokens[$i]);
    $sTdLexema = sprintf('<td>%s</td>', $aLexema[$i]);
    $sTdEstados = sprintf('<td>%s</td>', $aEstados[$i]);
    $sTdPosicao = sprintf('<td>%s</td>', $aPosicao[$i]);
    $aTrs[] = sprintf($sTr, $sTdToken, $sTdLexema, $sTdEstados, $sTdPosicao);
}
?>
<table>
    <thead>
        <th>Token</th>
        <th>Lexema</th>
        <th>Estado(s)</th>
        <th>Posição</th>
    </thead>
    <tbody>
        <?=implode('', $aTrs)?>
    </tbody>
</table>
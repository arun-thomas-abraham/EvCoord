<?php
//matrix math functions and tests
//vectors are one-dimensional arrays
//matrices are two-dimensional arrays, defined in row-column order, i.e. [row][col]

//print an array as an html table
function mat_print($A) {
    $n = count($A);
    $m = count($A[0]);
    print("<div><table style='border: 1px solid black'>");
    for ($i = 0; $i < $n; $i++) {
        print("<tr style='border: 1px solid black'>");
        for ($j = 0; $j < $m; $j++) {
            $val = $A[$i][$j];
            print("<td style='border: 1px solid black'>$val</td>");
        }
        print("</tr>");
    }
    print("</table></div>");
}

//returns a zero-initialized 2d matrix of size n*m.
function mat_zeros($n, $m) {
    $A = array();
    for ($i = 0; $i < $n; $i++) {
        $A[$i] = array();
        for ($j = 0; $j < $m; $j++) {
            $A[$i][$j] = 0.0;
        }
    }
    return $A;
}

//returns an identity matrix of size n
function mat_eye($n) {
    $A = mat_zeros($n, $n);
    for ($i = 0; $i < $n; $i++) {
        $A[$i][$i] = 1;
    }
    return $A;
}

//returns a randomly-initialized 2d matrix of size n*m from 0 to r.
function mat_rand($n, $m, $r) {
    $A = array();
    for ($i = 0; $i < $n; $i++) {
        $A[$i] = array();
        for ($j = 0; $j < $m; $j++) {
            $A[$i][$j] = rand(0, $r);
        }
    }
    return $A;
}

//transpose of matrix A
function mat_transpose($A) {
    $n = count($A);
    $m = count($A[0]);
    $At = mat_zeros($m, $n);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $At[$j][$i] = $A[$i][$j];
        }
    }
    return $At;
}

//two-norm of vector $a
function vec_norm($a) {
    $n = count($a);
    $ret = 0;
    for ($i = 0; $i < $n; $i++) {
        $ret += $a[$i]*$a[$i];
    }
    $ret = sqrt($ret);
    return $ret;
}

function vec_div($a, $b) {
    $n = count($a);
    $ret = $a;
    for ($i = 0; $i < $n; $i++) {
        $ret[$i] /= $b;
    }
    return $ret;
}

function vec_sub($a, $b) {
    $n = count($a);
    if (count($b) !== $n) {
        return null;
    }
    $ret = $a;
    for ($i = 0; $i < $n; $i++) {
        $ret[$i] -= $b[$i];
    }
    return $ret;
}

function vec_add($a, $b) {
    $n = count($a);
    if (count($b) !== $n) {
        return null;
    }
    $ret = $a;
    for ($i = 0; $i < $n; $i++) {
        $ret[$i] += $b[$i];
    }
    return $ret;
}

function vec_normalized($a) {
    $norm = vec_norm($a);
    if ($norm == 0) {
        return $a;
    }
    return vec_div($a, $norm);
}

//dot product between vectors a and b. they need to be the same size, obviously.
function vec_dot($a, $b) {
    $n = count($a);
    $ret = array();
    for ($i = 0; $i < $n; $i++) {
        $ret[$i] = (($a[$i])*($b[$i]));
    }
    return $ret;
}

//outer product between a and b.
function vec_outer($a, $b) {
    $n = count($a);
    $m = count($b);
    $ret = mat_zeros($n, $m);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $ret[$i][$j] = (($a[$i])*($b[$j]));
        }
    }
    return $ret;
}

//matrix multiplication of A@B
function mat_dot($A, $B) {
    $n = count($A);
    $m = count($A[0]);
    //check for improper size
    if (count($B) !== $m) {
        //print("<p>error: dimension mismatch between $n and $m</p>");
        return null;
    }
    $k = count($B[0]);

    //matrix now needs to be nxk
    $ret = mat_zeros($n, $k);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $k; $j++) {
            //sum up rows to columns
            for ($l = 0; $l < 10; $l++) {
                $ret[$i][$j] += $A[$i][$l]*$B[$l][$j];
            }
        }
    }
    return $ret;
}

//subtracts matrices a-b
function mat_sub($A, $B) {
    $n = count($A);
    $m = count($A[0]);
    if (count($B) !== $n || count($B[0]) !== $m) {
        //print("<p>ERROR mat_sub: size difference.</p>");
        return null;
    }
    //matrix needs to be nxm too
    $ret = mat_zeros($n, $m);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $ret[$i][$j] = (($A[$i][$j])-($B[$i][$j]));
        }
    }
    return $ret;
}

//helper function, gets an optimal permutation P and PA from matrix A
function mat_permute($A) {
    $n = count($A);
    $P = mat_eye($n);
    $PA = $A;
    for ($i = 0; $i < $n; $i++) {
        //find the maximum value in the ith subcolumn
        $max = 0;
        $max_elem = $i;
        for ($j = $i; $j < $n; $j++) {
            if ($PA[$j][$i] > $max) {
                $max = $PA[$j][$i];
                $max_elem = $j;
            }
        }
        //swap the two rows
        $P_temp = $P[$i];
        $PA_temp = $PA[$i];
        $P[$i] = $P[$max_elem];
        $PA[$i] = $PA[$max_elem];
        $P[$max_elem] = $P_temp;
        $PA[$max_elem] = $PA_temp;
    }
    return array($P, $PA);
}

//returns the PLU decomposition of matrix A
function mat_lu($A) {
    $n = count($A);
    $m = count($A[0]);
    if ($n !== $m) {
        return null;
    }
    list($P, $PA) = mat_permute($A);
    $L = mat_zeros($n, $n);
    $U = mat_zeros($n, $n);
    for ($i = 0; $i < $n; $i++) {
        //ith row of U
        for ($j = $i; $j < $n; $j++) {
            $U[$i][$j] = $PA[$i][$j];
        }
        //ith column of L
        for ($j = $i; $j < $n; $j++) {
            $L[$j][$i] = (($PA[$j][$i])/($U[$i][$i]));
        }
        //compute subsection
        $subsection = mat_zeros($n, $n);
        //special outer product hack with the ith upper corners being 0
        for ($j = ($i+1); $j < $n; $j++) {
            for ($k = ($i+1); $k < $n; $k++) {
                $subsection[$j][$k] = (($L[$j][$i])*($U[$i][$k]));
            }
        }
        $PA = mat_sub($PA, $subsection);
        //print("<p>L</p>");
        //mat_print($L);
        //print("<p>U</p>");
        //mat_print($U);
    }
    return array($P, $L, $U);
}

//solves LUx = Py through forward and backwards substitution.
function mat_plu_solve($P, $L, $U, $b) {
    //print("<p>solving plu</p>");
    $n = count($b);
    if (count($P) !== $n || count($P[0]) !== $n || count($L) !== $n || count($L[0]) !== $n || count($U) !== $n || count($U[0]) !== $n) {
        return null;
    }
    //print("<p>b</p>");
    $Pb = mat_dot($P, $b);
    //mat_print($b);
    //get y by forward substitution
    $y = $Pb;
    //print("<p>pb</p>");
    //mat_print($Pb);
    //for each row
    for ($i = 0; $i < $n; $i++) {
        //get the element
        $y[$i][0] = (($y[$i][0])/($L[$i][$i]));
        $L[$i][$i] = 1;
        //reciprocate the answer downwards
        for ($j = ($i+1); $j < $n; $j++) {
            $y[$j][0] = ($y[$j][0]) - (($L[$j][$i])*($y[$i][0]));
            $L[$j][$i] = 0;
        }
    }
    //print("<p>y</p>");
    //mat_print($y);
    //get x by backwards substitution
    $x = $y;
    //for each row
    for ($i = ($n-1); $i >= 0; $i--) {
        //get the element
        $x[$i][0] = (($x[$i][0])/($U[$i][$i]));
        $U[$i][$i] = 1;
        //reciprocate the answer downwards
        for ($j = ($i-1); $j >= 0; $j--) {
            $x[$j][0] = ($x[$j][0]) - (($U[$j][$i])*($x[$i][0]));
            $U[$j][$i] = 0;
        }
    }
    //print("<p>x</p>");
    //mat_print($x);
    return $x;
}
?>
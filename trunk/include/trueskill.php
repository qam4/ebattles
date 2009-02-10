<?php
// functions for Trueskill calculation.
//___________________________________________________________________

function Trueskill_update($epsilon,$beta, $A_mu, $A_sigma, $A_points, $B_mu, $B_sigma, $B_points)
{
    if($A_points>$B_points)
    {
        $winner_mu    = $A_mu;
        $winner_sigma = $A_sigma;
        $looser_mu    = $B_mu;
        $looser_sigma = $B_sigma;
    }
    elseif($A_points==$B_points)
    {
        // TBD
    }
    else
    {
        $winner_mu    = $B_mu;
        $winner_sigma = $B_sigma;
        $looser_mu    = $A_mu;
        $looser_sigma = $A_sigma;
    }

    $c_ij = sqrt(2*pow($beta,2) + pow($winner_sigma,2) + pow($looser_sigma,2));
    $t = ($winner_mu - $looser_mu)/$c_ij;
    $alpha = $epsilon / $c_ij;

    $N = 1/(sqrt(2*M_PI)) * exp(- pow(($t - $alpha),2) / 2) ;
    $Psi = cdf($t - $alpha);

    $v = $N / $Psi;
    $w = $v * ($v + ($t-$alpha));

    $v = $v / $c_ij;
    $w = $w / pow($c_ij,2);

    return array($v,$w);
}

function erf($x)
{
    $a = (8*(M_PI - 3))/(3*M_PI*(M_PI - 4));
    $x2 = $x * $x;

    $ax2 = $a * $x2;
    $num = (4/M_PI) + $ax2;
    $denom = 1 + $ax2;

    $inner = (-$x2)*$num/$denom;
    $erf2 = 1 - exp($inner);

    return sqrt($erf2);
}

function cdf($n)
{
    if($n < 0)
    {
        return (1 - erf($n / sqrt(2)))/2;
    }
    else
    {
        return (1 + erf($n / sqrt(2)))/2;
    }
}
?>

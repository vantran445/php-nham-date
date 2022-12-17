<?php namespace Vantran\PhpNhamDate\Adapters;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Solaris\MoonPhase;

class MoonPhaseAdapter extends MoonPhase
{
    public function __construct(int|float $timestamp) 
    {
        $this->timestamp = $timestamp;

        // Astronomical constants. 1980 January 0.0
        $epoch = 2444238.5;

        // Constants defining the Sun's apparent orbit
        $elonge = 278.833540;       // Ecliptic longitude of the Sun at epoch 1980.0
        $elongp = 282.596403;       // Ecliptic longitude of the Sun at perigee
        $eccent = 0.016718;         // Eccentricity of Earth's orbit
        $sunsmax = 1.495985e8;      // Semi-major axis of Earth's orbit, km
        $sunangsiz = 0.533128;      // Sun's angular size, degrees, at semi-major axis distance

        // Elements of the Moon's orbit, epoch 1980.0
        $mmlong = 64.975464;        // Moon's mean longitude at the epoch
        $mmlongp = 349.383063;      // Mean longitude of the perigee at the epoch
//        $mlnode = 151.950429;       // Mean longitude of the node at the epoch
//        $minc = 5.145396;           // Inclination of the Moon's orbit
        $mecc = 0.054900;           // Eccentricity of the Moon's orbit
        $mangsiz = 0.5181;          // Moon's angular size at distance a from Earth
        $msmax = 384401;            // Semi-major axis of Moon's orbit in km
//        $mparallax = 0.9507;        // Parallax at distance a from Earth
        $synmonth = 29.53058868;    // Synodic month (new Moon to new Moon)

        $this->synmonth = $synmonth;

        // date is coming in as a UNIX timstamp, so convert it to Julian
        $date = $this->timestamp / 86400 + 2440587.5;

        // Calculation of the Sun's position
        $Day = $date - $epoch;                                                  // Date within epoch
        $N = $this->fixangle((360 / 365.2422) * $Day);                          // Mean anomaly of the Sun
        $M = $this->fixangle($N + $elonge - $elongp);                           // Convert from perigee co-ordinates to epoch 1980.0
        $Ec = $this->kepler($M, $eccent);                                       // Solve equation of Kepler
        $Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
        $Ec = 2 * rad2deg(atan($Ec));                                           // True anomaly
        $Lambdasun = $this->fixangle($Ec + $elongp);                            // Sun's geocentric ecliptic longitude

        $F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent));     // Orbital distance factor
        $SunDist = $sunsmax / $F;                                               // Distance to Sun in km
        $SunAng = $F * $sunangsiz;                                              // Sun's angular size in degrees

        // Calculation of the Moon's position
        $ml = $this->fixangle(13.1763966 * $Day + $mmlong);                     // Moon's mean longitude
        $MM = $this->fixangle($ml - 0.1114041 * $Day - $mmlongp);               // Moon's mean anomaly
//        $MN = $this->fixangle($mlnode - 0.0529539 * $Day);                      // Moon's ascending node mean longitude
        $Ev = 1.2739 * sin(deg2rad(2 * ($ml - $Lambdasun) - $MM));              // Evection
        $Ae = 0.1858 * sin(deg2rad($M));                                        // Annual equation
        $A3 = 0.37 * sin(deg2rad($M));                                          // Correction term
        $MmP = $MM + $Ev - $Ae - $A3;                                           // Corrected anomaly
        $mEc = 6.2886 * sin(deg2rad($MmP));                                     // Correction for the equation of the centre
        $A4 = 0.214 * sin(deg2rad(2 * $MmP));                                   // Another correction term
        $lP = $ml + $Ev + $mEc - $Ae + $A4;                                     // Corrected longitude
        $V = 0.6583 * sin(deg2rad(2 * ($lP - $Lambdasun)));                     // Variation
        $lPP = $lP + $V;                                                        // True longitude
//        $NP = $MN - 0.16 * sin(deg2rad($M));                                    // Corrected longitude of the node
//        $y = sin(deg2rad($lPP - $NP)) * cos(deg2rad($minc));                    // Y inclination coordinate
//        $x = cos(deg2rad($lPP - $NP));                                          // X inclination coordinate

//        $Lambdamoon = rad2deg(atan2($y, $x)) + $NP;                             // Ecliptic longitude
//        $BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc)))); // Ecliptic latitude

        // Calculation of the phase of the Moon
        $MoonAge = $lPP - $Lambdasun;                                           // Age of the Moon in degrees
        $MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2;                          // Phase of the Moon

        // Distance of moon from the centre of the Earth
        $MoonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc)));

        $MoonDFrac = $MoonDist / $msmax;
        $MoonAng = $mangsiz / $MoonDFrac;                                       // Moon's angular diameter
//        $MoonPar = $mparallax / $MoonDFrac;                                     // Moon's parallax

        // Store results
        $this->phase = $this->fixangle($MoonAge) / 360;                         // Phase (0 to 1)
        $this->illumination = $MoonPhase;                                       // Illuminated fraction (0 to 1)
        $this->age = $synmonth * $this->phase;                                  // Age of moon (days)
        $this->distance = $MoonDist;                                            // Distance (kilometres)
        $this->diameter = $MoonAng;                                             // Angular diameter (degrees)
        $this->age_in_degrees = $MoonAge;                                       // Age of the Moon in degrees
        $this->sundistance = $SunDist;                                          // Distance to Sun (kilometres)
        $this->sundiameter = $SunAng;                                           // Sun's angular diameter (degrees)
    }

    /**
     * Chuyển đổi từ triển khai DateTimeInterface
     *
     * @param DateTimeInterface|null $datetime
     * @return MoonPhaseAdapter
     */
    public static function fromDateTime(?DateTimeInterface $datetime = null): MoonPhaseAdapter
    {
        if (!$datetime) {
            $datetime = new DateTime('now', new DateTimeZone('UTC'));
        }

        return new self($datetime->getTimestamp());
    }

    /**
     * Chuyển đổi từ số ngày Julian
     *
     * @param integer|float $jdn
     * @param integer $offset       phần bù giờ địa phương, tính bằng giây 
     * @return MoonPhaseAdapter
     */
    public static function fromJdn(int|float $jdn, int $offset): MoonPhaseAdapter
    {
        $timestamp = JulianAdapter::setJdn($jdn)->toTimestamp($offset);
        return new self($timestamp);
    }

    /**
     * Tìm các pha Mặt trăng từ nhóm thời gian nguyên thủy
     *
     * @param integer $offset   phần bù múi giờ địa phương, tính bằng giây
     * @param integer $Y        năm gồm 4 chữ số
     * @param integer $m        tháng từ 1 đến 12
     * @param integer $d        ngày từ 1 đến 31
     * @param integer $H        giờ từ 0 đến 23
     * @param integer $i        phút từ 0 đến 59
     * @param integer $s        giây từ 0 đến 59
     * @return MoonPhaseAdapter
     */
    public static function fromDateTimePrimitive(
        int $offset,
        int $Y,
        int $m,
        int $d,
        int $H = 0,
        int $i = 0,
        int $s = 0
    ): MoonPhaseAdapter
    {
        $jdAdapter = JulianAdapter::fromDateTimePrimitive($Y, $m, $d, $H, $i, $s);
        $timestamp = $jdAdapter->toTimestamp($offset);

        return new self($timestamp);
    }
}
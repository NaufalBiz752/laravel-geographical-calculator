<?php

namespace KMLaravel\GeographicalCalculator\Traits;

use Exception;

trait Distances
{
    use DataStorage;
    use Formatter;
    use Debugger;

    /**
     * Finding the distance of points using several given coordinate points.
     *
     * @throws Exception
     *
     * @return array
     *
     * @author karam mustafa
     * @author karam mustafa
     */
    public function getDistance()
    {
        foreach ($this->getPoints() as $index => $point) {
            // check if we are not arrive to the last point yet.
            if (isset($this->getPoints()[$index + 1])) {
                // init and calc sin and cos value
                $this->setSin($this->getAngle($point[0], $this->getPoints($index + 1)[0], 'sin'))
                    ->setCos($this->getAngle($point[0], $this->getPoints($index + 1)[0], 'cos'))
                    // set the position of this loop at the local storage.
                    ->setInStorage('position', ($index + 1).'-'.($index + 2))
                    // set first longitude.
                    ->setLongitude($point[1])
                    // set second longitude.
                    ->setLongitude($this->getPoints($index + 1)[1])
                    // set the formatted key that bind with the prefix config.
                    ->setInStorage(
                        'distance_key',
                        $this->formatDistanceKey($this->getFromStorage('position'))
                    )
                    // save the results.
                    ->setResult([$this->getFromStorage('distance_key') => $this->calcDistance()]);
            }
        }

        return $this->getResult();
    }

    /**
     * get the sin or cos values multiply.
     *
     * @param int    $firstLat
     * @param int    $secondLat
     * @param string $angle
     *
     * @return float
     *
     * @author karam mustafa
     */
    private function getAngle($firstLat, $secondLat, $angle = 'sin')
    {
        // convert the first value to radian and get result sin or cos method
        // convert the second value to radian and get result sin or cos method
        return $angle(deg2rad($firstLat)) * $angle(deg2rad($secondLat));
    }

    /**
     * get theta angle.
     *
     * @return float
     *
     * @author karam mustafa
     */
    private function getValueForAngleBetween()
    {
        return cos(deg2rad($this->getLongs()[0] - $this->getLongs()[1]));
    }

    /**
     * calculation distance process.
     *
     * @throws Exception
     *
     * @return array
     *
     * @author karam mustafa
     */
    private function calcDistance()
    {
        $distance = acos($this->getSin() + $this->getCos() * $this->getValueForAngleBetween());

        return $this->resolveDistanceWithUnits($this->correctDistanceValue(rad2deg($distance)));
    }

    /**
     * @param $distance
     *
     * @return float
     *
     * @author karam mustafa
     */
    private function correctDistanceValue($distance)
    {
        return $distance * 60 * 1.1515;
    }

    /**
     * check if user chose any units.
     *
     * @param float $distance
     *
     * @throws Exception
     *
     * @return array
     *
     * @author karam mustafa
     */
    private function resolveDistanceWithUnits($distance)
    {
        if (isset($this->getOptions()['units']) &&
            sizeof($this->getOptions('units')) > 0
        ) {
            // loop in each unit and solve the distance.
            foreach ($this->getOptions()['units'] as $unit) {
                // check if the unit isset.
                $this->checkIfUnitExists($unit)
                    // set the result in storage.
                    ->setInStorage($unit, $distance * $this->getUnits()[$unit]);
            }
        } else {
            // if there are not units, then get the default units property.
            $this->setInStorage('mile', $distance * $this->getUnits()['mile']);
        }
        // remove un required results and get the results from storage.
        return $this->removeFromStorage('position', 'distance_key')->getFromStorage();
    }
}

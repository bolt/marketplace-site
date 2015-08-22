<?php
namespace Bolt\Extensions\Helper;
use forxer\Gravatar\Gravatar;
use Symfony\Component\VarDumper\VarDumper;

class Bolt extends \Twig_Extension
{

    public $statusTemplate = '<div class="buildstatus ui icon button %s" data-content="%s"><i class="icon %s"></i><span class="version">%s</span> %s</div>';

    public function getFunctions()
    {
        return array(
            'buildStatus'  => new \Twig_Function_Method($this, 'buildStatus',['is_safe' => ['html']]),
            'gravatar'  => new \Twig_Function_Method($this, 'gravatar',['is_safe' => ['html']]),
            'dump'  => new \Twig_Function_Method($this, 'printDump',['is_safe' => ['html']])
        );
    }

    public function getFilters()
    {
        return array(
            'humanTime'  => new \Twig_SimpleFilter('humanTime', [$this, 'humanTime'])
        );
    }


    public function buildStatus($build, $options = [])
    {
        if(!$build || $build->testStatus === 'pending') {
            return sprintf($this->statusTemplate, 'warning', "This version is currently awaiting a test result", 'wait', "", 'not setup');
        }

        if($build->phpTarget) {
            $php = str_replace('php', '', $build->phpTarget);
            $php = substr_replace($php, ".", 1, 0);
            $php .= "+";
        } else {
            $php = "";
        }


        if($build->testStatus === 'approved') {
            return sprintf($this->statusTemplate, 'success', "This version is an approved build", 'checkmark', $php, $build->testStatus);
        }

        if($build->testStatus === 'failed') {
            return sprintf($this->statusTemplate, 'alert', "This version is not an approved build", 'remove', $php, $build->testStatus);
        }
    }

    public function gravatar($email, $options = [])
    {
        return Gravatar::image($email);
    }


    public function printDump($var)
    {
        return VarDumper::dump($var);
    }

    public function humanTime($time, $suffix='')
    {
        if ($time instanceof \DateTime) {
           $time = $time->getTimestamp();
        }
        
        if (!$time) {
            return 'never';
        }
        
        $time = time() - $time; // to get the time since that moment
        
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').$suffix;
        }

    }

    public function getName()
    {
        return 'bolt_helper';
    }

}

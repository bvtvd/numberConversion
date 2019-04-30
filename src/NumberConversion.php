<?php

namespace bvtvd;

class NumberConversion 
{
    protected $uppercase = false;
    protected $simple = true; // 简化, 一十一 => 十一

    protected $chnNumChar = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');

    protected $chnUnitSection = array('', '万', '亿', '万亿');

    protected $chnUnitChar = array('', '十', '百', '千');

    protected $chnValuePair = array(
        '十' => array(
            'value' => 10,
            'secUnit' => false,
        ),
        '百' => array(
            'value' => 100,
            'secUnit' => false,
        ),
        '千' => array(
            'value' => 1000,
            'secUnit' => false,
        ),
        '万' => array(
            'value' => 10000,
            'secUnit' => true,
        ),
        '亿' => array(
            'value' => 100000000,
            'secUnit' => true,
        )
    );

    protected $chnNumCharUppercase = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');

    protected $chnUnitSectionUppercase = array('', '万', '亿', '万亿');

    protected $chnUnitCharUppercase = array('', '拾', '佰', '仟');

    protected $chnValuePairUppercase = array(
        '拾' => array(
            'value' => 10,
            'secUnit' => false,
        ),
        '佰' => array(
            'value' => 100,
            'secUnit' => false,
        ),
        '仟' => array(
            'value' => 1000,
            'secUnit' => false,
        ),
        '万' => array(
            'value' => 10000,
            'secUnit' => true,
        ),
        '亿' => array(
            'value' => 100000000,
            'secUnit' => true,
        )
    );

    protected $simplePair = array(
        '一十' => '十',
        '一十一' => '十一',
        '一十二' => '十二',
        '一十三' => '十三',
        '一十四' => '十四',
        '一十五' => '十五',
        '一十六' => '十六',
        '一十七' => '十七',
        '一十八' => '十八',
        '一十九' => '十九',
    );

    protected $simplePairUppercase = array(
        '壹拾' => '拾',
        '壹拾壹' => '拾壹',
        '壹拾贰' => '拾贰',
        '壹拾叁' => '拾叁',
        '壹拾肆' => '拾肆',
        '壹拾伍' => '拾伍',
        '壹拾陆' => '拾陆',
        '壹拾柒' => '拾柒',
        '壹拾捌' => '拾捌',
        '壹拾玖' => '拾玖',
    );

    public function __construct($uppercase = false, $simple = true)
    {
        $this->setUppercase($uppercase);
        $this->setSimple($simple);
    }

    public function setUppercase($bool)
    {
        $this->uppercase = $bool;
    }

    public function setSimple($bool)
    {
        $this->simple = $bool;
    }


    protected function getChnNumChar($index)
    {
        return $this->uppercase ? $this->chnNumCharUppercase[$index] : $this->chnNumChar[$index];
    }

    protected function getChnUnitSection($index)
    {
        return $this->uppercase ? $this->chnUnitSectionUppercase[$index] : $this->chnUnitSection[$index];
    }

    protected function getChnUnitChar($index)
    {
        return $this->uppercase ? $this->chnUnitCharUppercase[$index] : $this->chnUnitChar[$index];
    }

    protected function getSimplePair()
    {
        return $this->uppercase ? $this->simplePairUppercase : $this->simplePair;
    }

    /**
     * 数字转中文
     */
    public function numberToChinese($number)
    {
        $unitPos = 0;
        $strIns;
        $needZero = false;

        $chnStr = array();

        while($number > 0 ){
            
            $section = $number % 10000;

            if($needZero){
                array_unshift($chnStr, $this->getChnNumChar(0));
            }

            $strIns = $this->sectionToChinese($section);
            // 是否需要节权位
            $strIns .= ($section != 0) ? $this->getChnUnitSection($unitPos) : $this->getChnUnitSection(0);
            array_unshift($chnStr, $strIns);
            // 千位  补零
            $needZero = ($section < 1000) && ($section > 0);
            $number = intval($number / 10000);
            $unitPos++;
        }

        return $this->after(join('', $chnStr));
    }

    /**
     * 后续处理
     */
    protected function after($string)
    {
        $string = $this->simpleHandler($string);

        return $string;
    }

    /**
     * 部分数字的简化处理
     */
    protected function simpleHandler($string)
    {
        if($this->simple){
            // 将一十一 => 十一 ..
            $pair = $this->getSimplePair();
            if(array_key_exists($string, $pair)){
                return $pair[$string];
            }

            // 将 五百一十 => 五百一
            $pattern = $this->uppercase ? '/(佰[壹贰叁肆伍陆柒捌玖])拾$/u' : '/(百[一二三四五六七八九])十$/u';

            return preg_replace($pattern, '$1', $string);
        }

        return $string;
    }

    /**
     * 节数字转中文
     */
    protected function sectionToChinese($section)
    {
        $strIns;
        $unitPos = 0;
        $zero = true;

        $chnStr = array();

        while($section > 0){
            $v = $section % 10;
            if(0 == $v){
                if((0 == $section) || !$zero){
                    $zero = true; // 需要补, $zero 的作用是确保对连续多个, 只补一个中文零
                    array_unshift($chnStr, $this->getChnNumChar($v));
                }
            }else{
                $zero = false;  // 至少有一个数字不是
                $strIns = $this->getChnNumChar($v);
                $strIns .= $this->getChnUnitChar($unitPos); // 对应的权位
                array_unshift($chnStr, $strIns);
            }
            $unitPos++;
            $section = intval($section / 10);
            // echo join('', $chnStr), "\n";
        }

        return join('', $chnStr);
    }

    /**
     * 中文转数字
     */
    public function chineseToNumber($string)
    {
        $rtn = 0;
        $section = 0;
        $number = 0;
        $secUnit = false;

        $stringArr = preg_split('/(?<!^)(?!$)/u', $string);

        while(0 < count($stringArr)){
            $chnNumber = array_shift($stringArr);
            $num = $this->chineseToValue($chnNumber);
            if(false === $num){// 单位
                
                if($temp = $this->chineseToUnit($chnNumber)){
    
                    $unit = $temp['value'];
                    $secUnit = $temp['secUnit'];

                    if($secUnit){   // 是节权位说明一个节已经结束
                        $section = ($section + $number) * $unit;
                        $rtn += $section;
                        $section = 0;
                    }else{
                        $section += $number > 0 ? ($number * $unit) : $unit;
                    }

                    $number = 0;

                    if(1 > count($stringArr)){
                        $rtn += $section;
                        break;
                    }
                }
                
            }else{// 数字

                $number = $num;
                if(1 > count($stringArr)){ // 如果是最后一位数组, 直接结束
                    $section += $number;
                    $rtn += $section;
                    break;
                }
            }
        }

        return $rtn;
    }

    protected function chineseToValue($string)
    {
        return $this->uppercase ? array_search($string, $this->chnNumCharUppercase) : array_search($string, $this->chnNumChar);
    }

    protected function chineseToUnit($string)
    {
        if($this->uppercase){
            return isset($this->chnValuePairUppercase[$string]) ? $this->chnValuePairUppercase[$string] : false;
        }
        return isset($this->chnValuePair[$string]) ? $this->chnValuePair[$string] : false;
    }
}

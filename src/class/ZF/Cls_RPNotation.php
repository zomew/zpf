<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2019/5/5
 * Time: 14:36
 * File: Cls_RPNotation.php
 */

namespace ZF;

/**
 * Class RPNotation
 *
 * @package ZF
 * @author  Lambert310
 * @see     https://blog.csdn.net/lambert310/article/details/77461047
 */
class RPNotation
{
    /**
     * 正则表达式，用于将表达式字符串，解析为单独的运算符和操作项
     */
    const PATTERN_EXP = '%((?:[a-zA-Z_]+)|(?:[\(\[\]\)\+\-\*/])|(?:[0-9]+(?:\.[0-9]+)?)){1}%';
    /**
     * 运算符优先级别
     */
    const EXP_PRIORITIES = ['+' => 1, '-' => 1, '*' => 2, '/' => 2, '(' => 0, ')' => 0, '[' => 0, ']' => 0,];

    /**
     * 计算字符串表达式主入口
     * @param string $exp        普通表达式，例如 a+b*(c+d)
     * @param array  $exp_values 表达式对应数据内容，例如 ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
     *
     * @return mixed|null
     * @static
     */
    public static function calculate($exp, $exp_values = [])
    {
        $exp_arr = self::parseExp($exp);//将表达式字符串解析为列表
        if (!is_array($exp_arr)) {
            return null;
        }
        $output_queue = self::nifix2rpn($exp_arr);
        return self::calculateValue($output_queue, $exp_values);
    }

    /**
     * 将字符串中每个操作项和预算符都解析出来
     * @param $exp
     *
     * @return mixed|null
     * @static
     */
    protected static function parseExp($exp)
    {
        $match = [];
        preg_match_all(self::PATTERN_EXP, $exp, $match);
        if ($match) {
            return $match[0];
        } else {
            return null;
        }
    }

    /**
     * 将中缀表达式转为后缀表达式
     * @param $input_queue
     *
     * @return array
     * @static
     */
    protected static function nifix2rpn($input_queue)
    {
        $exp_stack = [];
        $output_queue = [];
        foreach ($input_queue as $input) {
            if (in_array($input, array_keys(self::EXP_PRIORITIES))) {
                if ($input == '(' || $input == '[') {
                    array_push($exp_stack, $input);
                    continue;
                }
                if ($input == ')' || $input == ']') {
                    $tmp_exp = array_pop($exp_stack);
                    while ($tmp_exp && $tmp_exp != '(' && $tmp_exp != '[') {
                        array_push($output_queue, $tmp_exp);
                        $tmp_exp = array_pop($exp_stack);
                    }
                    continue;
                }
                foreach (array_reverse($exp_stack) as $exp) {
                    if (self::EXP_PRIORITIES[$input] <= self::EXP_PRIORITIES[$exp]) {
                        array_pop($exp_stack);
                        array_push($output_queue, $exp);
                    } else {
                        break;
                    }
                }
                array_push($exp_stack, $input);
            } else {
                array_push($output_queue, $input);
            }
        }
        foreach (array_reverse($exp_stack) as $exp) {
            array_push($output_queue, $exp);
        }
        return $output_queue;
    }

    /**
     * 传入后缀表达式队列、各项对应值的数组，计算出结果
     * @param $output_queue
     * @param $exp_values
     *
     * @return mixed|null
     * @static
     */
    protected static function calculateValue($output_queue, $exp_values)
    {
        $res_stack = [];
        foreach ($output_queue as $out) {
            if (in_array($out, array_keys(self::EXP_PRIORITIES))) {
                $a = array_pop($res_stack);
                $b = array_pop($res_stack);
                $res = 0;
                switch ($out) {
                    case '+':
                        $res = $b + $a;
                        break;
                    case '-':
                        $res = $b - $a;
                        break;
                    case '*':
                        $res = $b * $a;
                        break;
                    case '/':
                        $res = $b / $a;
                        break;
                }
                array_push($res_stack, $res);
            } else {
                if (is_numeric($out)) {
                    array_push($res_stack, floatval($out));
                } else {
                    array_push($res_stack, $exp_values[$out]);
                }
            }
        }
        return count($res_stack) == 1 ? $res_stack[0] : null;
    }
}

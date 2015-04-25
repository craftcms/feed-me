<?php
namespace Craft;

class UniqidTwigExtension extends \Twig_Extension
{
	public function getName()
	{
		return Craft::t('Uniqid');
	}

	public function getFunctions()
	{
		return array(
			'uniqid' => new \Twig_Function_Method($this, 'generateRandomString')
		);
	}

    public static function generateRandomString($length = 5)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
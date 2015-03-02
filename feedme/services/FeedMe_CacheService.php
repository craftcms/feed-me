<?php
namespace Craft;

class FeedMe_CacheService extends BaseApplicationComponent
{
	public function set($url, $value, $duration)
	{
		return craft()->cache->set(base64_encode(urlencode($url)), $value, $duration, null);
	}

	public function get($url)
	{
		return craft()->cache->get(base64_encode(urlencode($url)));
	}
}
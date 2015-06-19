<?php
namespace Craft;

class FeedMe_FeedNodeModel extends BaseModel
{
	function __toString()
	{
		return Craft::t($this->value);
	}

	protected function defineAttributes()
	{
		return array(
			'value' => AttributeType::Mixed,
			'attributes' => AttributeType::Mixed,
		);
	}
}

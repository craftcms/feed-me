<?php
namespace Craft;

class FeedMe_FeedModel extends BaseModel
{
	function __toString()
	{
		return Craft::t($this->name);
	}

	protected function defineAttributes()
	{
		return array(
			'id'				=> AttributeType::Number,
			'name'				=> AttributeType::String,
			'feedUrl'			=> AttributeType::Url,
			'feedType'			=> AttributeType::String,
			'primaryElement'	=> AttributeType::String,
			'section'			=> AttributeType::String,
			'entrytype'			=> AttributeType::String,
			'duplicateHandle'	=> AttributeType::String,
			'fieldMapping'		=> AttributeType::Mixed,
			'fieldUnique'		=> AttributeType::Mixed,
		);
	}
}

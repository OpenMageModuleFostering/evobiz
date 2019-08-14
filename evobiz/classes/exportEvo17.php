<?php
if (!defined('EVOBIZ'))
	exit;

class exportEvo17 extends exportEvo
{
	public function __construct()
	{
		define("WEBSITE_NAME",	Mage::app()->getWebsite()->getName());
		define("LOCALE_CODE",	Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()));
		define("STORE_NAME",	Mage::app()->getStore()->getName());
		define("STORE_ID",		Mage::app()->getStore()->getStoreId());
		define("STORE_CODE",	Mage::app()->getStore()->getCode());

		$a_url = parse_url(Mage::getBaseUrl());
		define("BASE_URL",	$a_url["path"]);


		define("EVOBIZ_EXPORT_FILE",accent_clean(strtolower(preg_replace("%(--*)%","-",str_replace(" ","-",WEBSITE_NAME."-".STORE_NAME."-".LOCALE_CODE.".xml")))));
		define("EVOBIZ_EXPORT_PATH",EVOBIZ_EXPORT_DIR."/".EVOBIZ_EXPORT_FILE);

		e("EVOBIZ_EXPORT_FILE ".EVOBIZ_EXPORT_FILE);

		parent::__construct();
	}

	public function getCountProducts()
	{
		return Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addStoreFilter(STORE_ID)
			->addAttributeToFilter('visibility',
				array
				(
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
				)
			)
 			->addAttributeToFilter('status', 1)
			->count()
		;
	}

	public function export()
	{
		$products = Mage::getModel('catalog/product')
			->getCollection()
 			->addAttributeToSelect('*')
			->addAttributeToSort('id', 'ASC')
// 			->addAttributeToSort('type_id', 'ASC')
			->addStoreFilter(STORE_ID)
			->addAttributeToFilter('visibility',
				array
				(
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
				)
			)
			->addAttributeToFilter('status', 1)
			->setPageSize($this->pageSize)
			->setCurPage($this->pageNumber)
		;

// 		pr($products);
// 		pr($products->getSelect()->__toString());

// 		Mage::log('Does this work');

		foreach ($products as $id => $product)
		{
			$productType = $product->getTypeId();

// 			e("$productType ".$product->getId());
// 				continue;

			if(!($productType == "simple" OR $productType == "configurable" /*OR $productType == "grouped"*/))
				continue;

			if($productType == "simple")
			{
				e("===================================");
				e("$productType ".$product->getId());
 				echo(evo::indent(evo::XML()->product($this->base($product))));
			}
			else
			if($productType == "configurable")
			{
				e("++++++++++++++++++++++++++++++++++++");
				e("$productType ".$product->getId());

				$attributes  = $product->getTypeInstance()->getConfigurableAttributes();

				$a_attirbutes = array();
				foreach($attributes as $attribute)
				{
					$options = $attribute->getProductAttribute()->getSource()->getAllOptions(false);

					if(count($options))
					{
						$val = $attribute->getProductAttribute()->getAttributeCode();

						foreach($options as $option)
						{
							$a_attirbutes[$val][$option['value']] = $option['label'];
						}
					}
				}


				$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
				$col = $conf->getUsedProductCollection()
							->addAttributeToSelect('*')
							->addFilterByRequiredOptions()
// 							->addAttributeToFilter('visibility',
// 								array
// 								(
// 									Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
// 									Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
// 									Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
// 								)
// 							)
							->addAttributeToFilter('status', 1)
				;

				if(count($col))
				foreach($col as $simple_product)
				{
// 					if(!is_object($simple_product)) continue;

					e("°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°");
					e("$productType ".$simple_product->getId());


					$model = Mage::getModel('catalog/product'); //getting product model

					$productChild = $model->load($simple_product->getId());

					$xml_product = $this->base($simple_product,$product);

					if(count(array_keys($a_attirbutes)))
					{
						$xml_attribut = "";

						foreach(array_keys($a_attirbutes) as $attr)
						{
							$method = to_camel_case($attr)."D";

							$xml_attribut .= evo::XML()->$method($a_attirbutes[$attr][$simple_product->$attr]);
						}
					}

					if(!empty($xml_attribut))
					{
						$xml_product .= evo::XML()->attributs($xml_attribut);
					}


 					echo(evo::indent(evo::XML()->product($xml_product)));
				}

// 				exit;
			}
		}
	}



	function base($product,$productParent = null)
	{
		$xml_product = "";


		$model = Mage::getModel('catalog/product'); //getting product model

		$model->load($product->getId());
// 		e($model->getName());

		$parent = $this->parentProductId($product);



		$categoryPath = "";
		$a_cat = $parent->getCategoryIds();
		$_category = Mage::getModel('catalog/category')->load($a_cat[0]);
// 		pr($_category);
		$caturl=explode('.html', $_category->getUrlPath(false));


		if(isset($a_cat[0]))
		{

			($url2 = Mage::getBaseUrl().trim($caturl[0],"/")."/".trim(Mage::getModel('catalog/product_url')->getUrlPath($parent,null),"/"));

// 			e("°°°°°°°°");
			$a = array();

			$category = Mage::getModel('catalog/category')->load($a_cat[0]);
			$coll = $category->getResourceCollection();
			$pathIds = $category->getPathIds();
			$coll->addAttributeToSelect('name');
			$coll->addAttributeToFilter('entity_id', array('in' => $pathIds));

			$n = 0;
			foreach ($coll as $cat)
			{
 				if($n > 0)
				{
					$a[] = $cat->getName();
				}

				$n++;
			}

			e($categoryPath = implode(" > ",$a));
			$xml_product .= evo::XML()->categoryPathD($categoryPath);
			$xml_product .= evo::XML()->linkWithCategoriesD($url2);
		}
		else
		{
			$xml_product .= evo::XML()->categoryPathD("");
			$xml_product .= evo::XML()->linkWithCategoriesD("");
		}


		($url = Mage::getBaseUrl().trim(Mage::getModel('catalog/product_url')->getUrlPath($parent,null),"/"));
		$xml_product .= evo::XML()->linkD($url);


		$product_id = $product->getId();


		$parent_id = 0;
		if($productParent)
		{
			$parent_id = $productParent->getId();

			$xml_product .= evo::XML()->evoVariationD("VAR_".sprintf("%06d%06d",$parent_id,0));
			$xml_product .= evo::XML()->evoSkuD("SKU_".sprintf("%06d%06d",$parent_id,$product_id));
//  			$xml_product .= evo::XML()->evoTypeD("has parent");

		}
		else
		{
			$xml_product .= evo::XML()->evoVariationD("VAR_".sprintf("%06d%06d",$product_id,0));
			$xml_product .= evo::XML()->evoSkuD("SKU_".sprintf("%06d%06d",$product_id,0));
//  			$xml_product .= evo::XML()->evoTypeD("has no parent");
		}


		e($model->getName());

		$xml_product .= evo::XML()->nameD($model->getName());

// 		$xml_product .= evo::XML()->productTypeD($product->getTypeId());

		$xml_product .= evo::XML()->descriptionD($product->getDescription());
		$xml_product .= evo::XML()->descriptionShortD($product->getShortDescription());
		$xml_product .= evo::XML()->inDepthD($product->getInDepth());

		$xml_product .= evo::XML()->modelD($product->getModel());
		$xml_product .= evo::XML()->imageD($product->getImageUrl());


		$xml_product .= evo::XML()->priceD(sprintf("%0.2f",Mage::helper('tax')->getPrice($product, $product->getPrice())));
		$xml_product .= evo::XML()->priceFinalD(sprintf("%0.2f",Mage::helper('tax')->getPrice($product, $product->getFinalPrice())));

		$xml_product .= evo::XML()->weightD($product->getWeight());
		$xml_product .= evo::XML()->EAND($product->getEan_13());
 		$xml_product .= evo::XML()->skuD($product->getSku());

		e($product->getEan_13());

		$xml_product .= evo::XML()->BrandD($product->brand != ""?$product->getAttributeText('brand'):"");
		$xml_product .= evo::XML()->ManufacturerD($product->manufacturer != ""?$product->getAttributeText('manufacturer'):"");

		$o_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

		$stock = $o_stock->getQty();
		$xml_product .= evo::XML()->inStockD(($stock > 0)?"1":"0");
		$xml_product .= evo::XML()->quantityD($stock);


		$to_include = dirname(EVOBIZ_ROOT)."/evobiz.php";

 		if(file_exists($to_include))
 		{
			include($to_include);
		}

// 		$this->getShippingEstimate($product);

		return $xml_product;
	}


	public function parentProductId($product)
	{
		$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

		if(isset($parentIds[0]))
		{
			$parent = Mage::getModel('catalog/product')->load($parentIds[0]);

			if($product->getId() == $parent->getId())
			{
				return $parent;
			}
			else
			{
				return $this->parentProductId($parent);
			}
		}

		return $product;

	}

	function getShippingEstimate($product)
	{
		$quote = Mage::getModel('sales/quote')->setStoreId(STORE_ID);


 		$product->getStockItem()->setData('qty', 1)->setData('is_in_stock', 1)->setData('manage_stock', 0)->setData('use_config_manage_stock', 0)->setData('min_sale_qty', 0)->setData('use_config_min_sale_qty', 0)->setData('max_sale_qty', 1000)->setData('use_config_max_sale_qty', 0);

		vd($product->getId());

		$o_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

		e($o_stock->getQty());

		try
		{
			$quote->addProduct($product,1);

			$quote->getShippingAddress()->setCountryId(strtoupper(STORE_CODE));
			$quote->getShippingAddress()->collectTotals();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->getShippingAddress()->collectShippingRates();

			$_rates = $quote->getShippingAddress()->getShippingRatesCollection();

			$shippingRates = array();
			foreach ($_rates as $_rate):
					if($_rate->getPrice() > 0) {
						$shippingRates[] =  array("Title" => $_rate->getMethodTitle(), "Price" => $_rate->getPrice());
					}
			endforeach;

			pr($shippingRates);
		}
		catch (Exception $e)
		{

		}
	}



	function getShippingPrice($product)
	{
// 		$carrier = Mage::getConfiggetConfig()->getConfigData('default_shipping_method');

		$shippingPrice = 0;
		if(!empty($carrier))
		{

			$countryCode = Mage::app()->getConfig()->getConfigData('shipping_price_based_on');
			$shippingPrice = Mage::app()->getHelper()->getShippingPrice($product,$carrier,$countryCode);
		}

		if(!$shippingPrice)
		{
			$shippingPrice = Mage::app()->getConfig()->getConfigData('default_shipping_price');
		}

		return $shippingPrice;
	}

}


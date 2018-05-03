<?php
namespace Avon;

class Catalog {
	
	private $modx;
	public $compaing = false;
	public $leftDay = false;
	public $cdnPath = false;
	
	public function __construct(\DocumentParser $modx) {
		$this->modx = $modx;
		$this->dirPermissions = octdec($this->modx->config['new_folder_permissions']);
		$this->filePermissions = octdec($this->modx->config['file_permissions']);
	}
	
	private function getCURL(string $url, string $referer = "https://my.avon.ru/jelektronnyj-katalog/")
	{
		//return false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
			"Cache-Control: no-cache",
			"Connection: keep-alive",
			"Host: my.avon.ru",
			"Pragma: no-cache",
			"Referer: " . $referer,
			"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36 OPR/52.0.2871.64"
		));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		$return = ($output === FALSE) ? FALSE : $output;
		curl_close($ch);
		return $return;
	}
	
	public function getShopContext(){
		$re = '/var _ShopContext=(.*});/sUmi';
		$this->compaing = false;
		$this->leftDay = false;
		$this->cdnPath = false;
		$file = $this->getCURL('https://my.avon.ru/jelektronnyj-katalog/');
		if($file):
			preg_match($re, $file, $matches);
			if($matches[1]):
				$avon = json_decode($matches[1]);
				if($avon):
					$this->compaing = $avon->CampaignNumber;
					$this->leftDay = $avon->CampaignDaysLeft;
					$re_1 = '/\[CULTURE\]/i';
					$re_2 = '/\[CAMPAIGN\]/i';
					$this->cdnPath = preg_replace($re_2, $avon->CampaignNumber, preg_replace($re_1, 'ru-ru', $avon->BrochureViewData->BrochureRootUrlFormat, 1), 1);
					return true;
				endif;
			endif;
		endif;
		return false;
	}
	
	public function getCatalogs() {
		if($this->compaing):
			$file = $this->getCURL('https://my.avon.ru/api/brochureapi/BrochureSummariesJson?campaignNumber=' . $this->compaing . '&language=ru&market=RU');
			if($file):
				$avon = json_decode($file);
				if($avon):
					if($avon->Data):
						return $avon->Data;
					endif;
				endif;
			endif;
		endif;
		return false;
	}
}
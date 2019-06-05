<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Detector;

use Nette;
use SixtyEightPublishers;

final class NetteRequestDetector implements IDetector
{
	use Nette\SmartObject;

	/** @var \Nette\Http\IRequest  */
	private $request;

	/**
	 * @param \Nette\Http\IRequest $request
	 */
	public function __construct(Nette\Http\IRequest $request)
	{
		$this->request = $request;
	}

	/************* interface \SixtyEightPublishers\i18n\Detector\IDetector *************/

	/**
	 * {@inheritdoc}
	 */
	public function detect(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profileContainer): ?SixtyEightPublishers\i18n\Profile\IProfile
	{
		$url = $this->request->getUrl()->getAbsoluteUrl();

		foreach ($profileContainer->toArray() as $profile) {
			if (FALSE === $profile->isEnabled()) {
				continue;
			}

			foreach ($profile->getDomains() as $domain) {
				$matches = null;
				if ($domain === $url || (FALSE !== preg_match('#' . $domain . '#', $url, $matches) && !empty($matches))) {
					return $profile;
				}
			}
		}

		return NULL;
	}
}

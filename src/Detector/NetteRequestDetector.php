<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Detector;

use Nette\SmartObject;
use Nette\Http\IRequest;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

final class NetteRequestDetector implements DetectorInterface
{
	use SmartObject;

	/** @var \Nette\Http\IRequest  */
	private $request;

	/**
	 * @param \Nette\Http\IRequest $request
	 */
	public function __construct(IRequest $request)
	{
		$this->request = $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public function detect(ProfileContainerInterface $profileContainer): ?ProfileInterface
	{
		$url = $this->request->getUrl()->getAbsoluteUrl();

		foreach ($profileContainer->toArray() as $profile) {
			if (FALSE === $profile->isEnabled()) {
				continue;
			}

			foreach ($profile->getDomains() as $domain) {
				$matches = NULL;
				if ($domain === $url || (FALSE !== preg_match('#' . $domain . '#', $url, $matches) && !empty($matches))) {
					return $profile;
				}
			}
		}

		return NULL;
	}
}

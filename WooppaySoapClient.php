<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2012-2015 Wooppay
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

class WooppaySoapClient
{
	private $c;

	public function __construct($url, $options = array())
	{
		try {
			$this->c = new SoapClient($url, $options);
		} catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		if (empty($this->c)) {
			throw new WooppaySoapException('Cannot create instance of Soap client');
		}
	}

	/**
	 * @param $method
	 * @param $data
	 * @return WooppaySoapResponse
	 * @throws BadCredentialsException
	 * @throws UnsuccessfulResponseException
	 * @throws WooppaySoapException
	 */
	public function __call($method, $data)
	{
		try {

			$response = $this->c->$method($data[0]);
		} catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		$response = new WooppaySoapResponse($response);
		switch ($response->error_code) {
			case 0:
				return $response;
				break;
			case 5:
				throw new BadCredentialsException();
				break;
			default:
				throw new UnsuccessfulResponseException('Error code ' . $response->error_code, $response->error_code);
		}

	}

	/**
	 * @param string $login
	 * @param string $pass
	 * @return boolean
	 * @throws BadCredentialsException
	 * @throws UnsuccessfulResponseException
	 * @throws WooppaySoapException
	 */
	public function login($login, $pass)
	{
		$login_request = new CoreLoginRequest();
		$login_request->username = $login;
		$login_request->password = $pass;
		$response = $this->core_login($login_request);

		if (isset($response->response->session)) {
			$this->c->__setCookie('session', $response->response->session);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param int $operationId
	 * @return CashGetOperationDataResponse
	 * @throws UnsuccessfulResponseException
	 * @throws WooppaySoapException
	 */
	public function getOperationData($operationId)
	{
		$data = new CashGetOperationDataRequest();
		$data->operationId = array($operationId);
		return $this->cash_getOperationData($data);
	}

	/**
	 * @param string $referenceId
	 * @param string $backUrl
	 * @param string $requestUrl
	 * @param float $amount
	 * @param string $serviceName
	 * @param string $addInfo
	 * @param string $deathDate
	 * @param string $description
	 * @param string $userEmail
	 * @param string $userPhone
	 * @param int $serviceType
	 * @return CashCreateInvoiceResponse
	 */
	public function createInvoice($referenceId, $backUrl, $requestUrl, $amount, $serviceName = '', $addInfo = '', $deathDate = '', $description = '', $userEmail = '', $userPhone = '', $serviceType = 4)
	{
		$data = new CashCreateInvoiceByServiceRequest();
		$data->referenceId = $referenceId;
		$data->backUrl = $backUrl;
		$data->requestUrl = $requestUrl;
		$data->amount = (float)$amount;
		$data->addInfo = $addInfo;
		$data->deathDate = $deathDate;
		$data->description = $description;
		$data->userEmail = $userEmail;
		$data->userPhone = $userPhone;
		$data->serviceName = $serviceName;
		$data->serviceType = $serviceType;
		return $this->cash_createInvoiceByService($data);
	}

	public function getLastDialog()
	{
		return array('req' => $this->c->__getLastRequest(), 'res' => $this->c->__getLastResponse());
	}
}

class CoreLoginRequest
{
	/**
	 * @var string $username
	 * @soap
	 */
	public $username;
	/**
	 * @var string $password
	 * @soap
	 */
	public $password;
	/**
	 * @var string $captcha
	 * @soap
	 */
	public $captcha = null;
}

class CashGetOperationDataRequest
{
	/**
	 * @var $operationId array
	 */
	public $operationId;

}

class CashCreateInvoiceRequest
{
	/**
	 * @var string $referenceId
	 * @soap
	 */
	public $referenceId;
	/**
	 * @var string $backUrl
	 * @soap
	 */
	public $backUrl;
	/**
	 * @var string $requestUrl
	 * @soap
	 */
	public $requestUrl = '';
	/**
	 * @var string $addInfo
	 * @soap
	 */
	public $addInfo;
	/**
	 * @var float $amount
	 * @soap
	 */
	public $amount;
	/**
	 * @var string $deathDate
	 * @soap
	 */
	public $deathDate;
	/**
	 * @var int $serviceType
	 * @soap
	 */
	public $serviceType = null;
	/**
	 * @var string $description
	 * @soap
	 */
	public $description = '';
	/**
	 * @var int $orderNumber
	 * @soap
	 */
	public $orderNumber = null;
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = null;
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = null;
}

class CashCreateInvoiceExtendedRequest extends CashCreateInvoiceRequest
{
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = '';
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = '';
}

class CashCreateInvoiceExtended2Request extends CashCreateInvoiceExtendedRequest
{
	/**
	 * @var int $cardForbidden
	 * @soap
	 */
	public $cardForbidden;
}

class CashCreateInvoiceByServiceRequest extends CashCreateInvoiceExtended2Request {
	/**
	 * @var string $serviceName
	 * @soap
	 */
	public $serviceName;
}

class WooppaySoapResponse
{

	public $error_code;
	public $response;

	public function __construct($response)
	{

		if (!is_object($response)) {
			throw new BadResponseException('Response is not an object');
		}

		if (!isset($response->error_code)) {
			throw new BadResponseException('Response do not contains error code');
		}
		$this->error_code = $response->error_code;

		if (!property_exists($response, 'response')) {
			throw new BadResponseException('Response do not contains response body');
		}
		$this->response = $response->response;
	}
}

class BaseResponse
{
	/**
	 * @var int $error_code
	 * @soap
	 */
	public $error_code;
}

class CashCreateInvoiceResponse extends BaseResponse
{
	/**
	 * @var CashCreateInvoiceResponseData $response
	 * @soap
	 */
	public $response;
}

class CashCreateInvoiceResponseData
{
	/**
	 * @var int $operationId
	 * @soap
	 */
	public $operationId;
	/**
	 * @var string $operationUrl
	 * @soap
	 */
	public $operationUrl;
}

class CashGetOperationDataResponse extends BaseResponse
{
	/**
	 * @var CashGetOperationDataResponseData $response
	 * @soap
	 */
	public $response;
}

class CashGetOperationDataResponseData
{
	/**
	 * @var CashGetOperationDataResponseDataRecord[] $records
	 * @soap
	 */
	public $records;
}

class CashGetOperationDataResponseDataRecord
{
	/**
	 * @var int $id
	 * @soap
	 */
	public $id;
	/**
	 * @var int $type
	 * @soap
	 */
	public $type;
	/**
	 * @var int $lotId
	 * @soap
	 */
	public $lotId;
	/**
	 * @var float $sum
	 * @soap
	 */
	public $sum;
	/**
	 * @var string $date
	 * @soap
	 */
	public $date;
	/**
	 * @var int $status
	 * @soap
	 */
	public $status;
	/**
	 * @var string $comment
	 * @soap
	 */
	public $comment;
	/**
	 * @var string $fromSubject
	 * @soap
	 */
	public $fromSubject;
	/**
	 * @var string $toSubject
	 * @soap
	 */
	public $toSubject;
	/**
	 * @var string $fromFullName
	 * @soap
	 */
	public $fromFullName;
	/**
	 * @var string $toFullName
	 * @soap
	 */
	public $toFullName;
}

class WooppayOperationStatus
{
	/**
	 * Новая
	 */
	const OPERATION_STATUS_NEW = 1;
	/**
	 * На рассмотрении
	 */
	const OPERATION_STATUS_CONSIDER = 2;
	/**
	 * Отклонена
	 */
	const OPERATION_STATUS_REJECTED = 3;
	/**
	 * Проведена
	 */
	const OPERATION_STATUS_DONE = 4;
	/**
	 * Сторнирована
	 */
	const OPERATION_STATUS_CANCELED = 5;
	/**
	 * Сторнирующая
	 */
	const OPERATION_STATUS_CANCELING = 6;
	/**
	 * Удалена
	 */
	const OPERATION_STATUS_DELETED = 7;
	/**
	 * На квитовании
	 */
	const OPERATION_STATUS_KVITOVANIE = 4;
	/**
	 * На ожидании подверждения или отказа мерчанта
	 */
	const OPERATION_STATUS_WAITING = 9;
}

class WooppaySoapException extends Exception
{
}
class BadResponseException extends WooppaySoapException
{
}
class UnsuccessfulResponseException extends WooppaySoapException
{
}
class BadCredentialsException extends UnsuccessfulResponseException
{
}

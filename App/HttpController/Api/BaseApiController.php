<?php


namespace App\HttpController\Api;


use App\HttpController\BaseController;
use App\Service\Exception\AuthException;
use App\Service\Exception\ServiceException;
use App\Service\Exception\ValidateException;
use EasySwoole\Http\Message\Status;
use Throwable;

abstract class BaseApiController extends BaseController
{

    /**
     * queryPage
     *
     * @param string $fieldName
     * @return int
     */
    protected function queryPage(string $fieldName = 'page'): int
    {
        $page = $this->request()->getQueryParam($fieldName) ?? 1;
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        return $page;
    }

    /**
     * queryPageSize
     *
     * @param string $fieldName
     * @return int
     */
    protected function queryPageSize(string $fieldName = 'size'): int
    {
        $size = $this->request()->getQueryParam($fieldName);
        if (!is_numeric($size) || $size < 1 ) {
            $size = 20;
        }

        return $size > 100 ? 100 : $size;
    }

    /**
     * onException
     *
     * @param Throwable $throwable
     * @throws Throwable
     */
    protected function onException(Throwable $throwable): void
    {
        /** 验证类异常 */
        if ($throwable instanceof ValidateException) {
            $this->writeJsonResponse(Status::CODE_BAD_REQUEST, null, $throwable->getErrorMessage());
            return;
        }

        /** 鉴权类异常 */
        if ($throwable instanceof AuthException) {
            $this->writeJsonResponse(Status::CODE_UNAUTHORIZED, null, $throwable->getErrorMessage());
            return;
        }

        /** 测试环境直接输出异常 */
        if ($this->isDev()) {
            $this->writeTextResponse(Status::CODE_INTERNAL_SERVER_ERROR, $throwable->__toString());
            return;
        }

        if ($throwable instanceof ServiceException) {
            $this->writeJsonResponse(Status::CODE_INTERNAL_SERVER_ERROR, null, $throwable->getErrorMessage() ?? '服务器开小差了，请稍后再试！');
        } else {
            $this->writeJsonResponse(Status::CODE_INTERNAL_SERVER_ERROR, null, '服务器开小差了，请稍后再试！');
        }

        /** 上层处理 */
        parent::onException($throwable);
    }
}
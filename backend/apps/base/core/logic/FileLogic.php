<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/12 10:57
 * @description
 */

namespace apps\base\core\logic;


class FileLogic
{

    public function file($id, $host = '', $scheme = '')
    {
        return base_api_post('file', '/file/file', [
            'id' => $id,
            'host' => $host,
            'scheme' => $scheme
        ]);
    }

    public function batchInfo(array $ids, $host = '', $scheme = '')
    {
        return base_api_post('file', '/file/batch_info', [
            'ids' => implode('|', $ids),
            'host' => $host,
            'scheme' => $scheme
        ]);
    }

    public function genToken($ext = '')
    {
        return base_api_post('file', '/file/gen_token', [
            'ext' => $ext
        ]);
    }

    public function delete($fileId)
    {
        if(is_array($fileId)) {
            return base_api_post('file', '/file/batch_delete', [
                'ids' => implode('|', $fileId)
            ]);
        } else {
            return base_api_post('file', '/file/delete', [
                'id' => $fileId
            ]);
        }
    }

    /**
     * 上传文件
     * @param string $fileData          --文件的base64字符串，例：data:image/png;base64,iVBORw0KGgoAAAANSU
     * @return array
     * @throws \think\Exception
     */
    public function upload($fileData)
    {
        return base_api_post('file', '/file/upload', [
            'fileData' => $fileData
        ]);
    }
}
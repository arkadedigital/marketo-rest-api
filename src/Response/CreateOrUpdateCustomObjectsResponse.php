<?php


namespace Arkadedigital\Marketo\Response;

use Arkadedigital\Marketo\Response;


class CreateOrUpdateCustomObjectsResponse extends Response
{


    /**
     * Override success function as Marketo incorrectly responds 'success'
     * even if the lead ID does not exist. Overriding it makes it consistent
     * with other API methods such as getList.
     *
     * @return bool
     */
    public function isSuccess()
    {
//        return parent::isSuccess()? count($this->getResult()) > 0: false;

        $error = false;
        foreach ($this->getResult() as $row) {
            if ($row['status'] == 'skipped') {
                $error = true;
            }
        }

        if ($error === true) {
            return false;
        }

        return true;
    }
    
    /**
     * Get the status of a lead. If no lead ID is given, it returns the status of the first lead returned.
     *
     * @param $id
     * @return bool
     */
    public function getStatus($id = null)
    {
        if ($this->isSuccess()) {
            if (!$id) {
                $result = $this->getResult();
                return $result[0]['status'];
            }

            foreach ($this->getResult() as $row) {
                if ($row['id'] == $id) {
                    return $row['status'];
                }
            }
        }

        return false;
    }

    /**
     * @return int|false
     */
    public function getId()
    {
        if ($this->isSuccess()) {
            $result = $this->getResult();
            return $result[0]['id'];
        }
        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        // if it's successful, don't return an error message
        if ($this->isSuccess()) {
            return null;
        }

        // if an error has been returned by Marketo, return that
        if ($error = parent::getError()) {
            return $error;
        }

        // if it's not successful and there's no error from Marketo, create one
        return array(
            'code' => '',
            'message' => 'Custom Object failed'
        );
    }
}

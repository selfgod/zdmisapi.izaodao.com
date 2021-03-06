<?php
namespace Services\Order;

/**
 * Autogenerated by Thrift Compiler (0.11.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


class JsonResult {
  static $isValidate = false;

  static $_TSPEC = array(
    1 => array(
      'var' => 'code',
      'isRequired' => false,
      'type' => TType::I32,
      ),
    2 => array(
      'var' => 'msg',
      'isRequired' => false,
      'type' => TType::STRING,
      ),
    3 => array(
      'var' => 'data',
      'isRequired' => false,
      'type' => TType::MAP,
      'ktype' => TType::STRING,
      'vtype' => TType::STRING,
      'key' => array(
        'type' => TType::STRING,
      ),
      'val' => array(
        'type' => TType::STRING,
        ),
      ),
    );

  /**
   * @var int
   */
  public $code = 200;
  /**
   * @var string
   */
  public $msg = "";
  /**
   * @var array
   */
  public $data = array(
  );

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['code'])) {
        $this->code = $vals['code'];
      }
      if (isset($vals['msg'])) {
        $this->msg = $vals['msg'];
      }
      if (isset($vals['data'])) {
        $this->data = $vals['data'];
      }
    }
  }

  public function getName() {
    return 'JsonResult';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->code);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->msg);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::MAP) {
            $this->data = array();
            $_size0 = 0;
            $_ktype1 = 0;
            $_vtype2 = 0;
            $xfer += $input->readMapBegin($_ktype1, $_vtype2, $_size0);
            for ($_i4 = 0; $_i4 < $_size0; ++$_i4)
            {
              $key5 = '';
              $val6 = '';
              $xfer += $input->readString($key5);
              $xfer += $input->readString($val6);
              $this->data[$key5] = $val6;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('JsonResult');
    if ($this->code !== null) {
      $xfer += $output->writeFieldBegin('code', TType::I32, 1);
      $xfer += $output->writeI32($this->code);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->msg !== null) {
      $xfer += $output->writeFieldBegin('msg', TType::STRING, 2);
      $xfer += $output->writeString($this->msg);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->data !== null) {
      if (!is_array($this->data)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('data', TType::MAP, 3);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->data));
        {
          foreach ($this->data as $kiter7 => $viter8)
          {
            $xfer += $output->writeString($kiter7);
            $xfer += $output->writeString($viter8);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}



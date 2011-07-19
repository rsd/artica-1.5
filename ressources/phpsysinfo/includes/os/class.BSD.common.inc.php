<?php
/***************************************************************************
*   Copyright (C) 2008 by phpSysInfo - A PHP System Information Script    *
*   http://phpsysinfo.sourceforge.net/                                    *
*                                                                         *
*   This program is free software; you can redistribute it and/or modify  *
*   it under the terms of the GNU General Public License as published by  *
*   the Free Software Foundation; either version 2 of the License, or     *
*   (at your option) any later version.                                   *
*                                                                         *
*   This program is distributed in the hope that it will be useful,       *
*   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
*   GNU General Public License for more details.                          *
*                                                                         *
*   You should have received a copy of the GNU General Public License     *
*   along with this program; if not, write to the                         *
*   Free Software Foundation, Inc.,                                       *
*   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
***************************************************************************/
//
// $Id: class.BSD.common.inc.php,v 1.61 2008/06/05 18:42:30 bigmichi1 Exp $
//
if (!defined('IN_PHPSYSINFO')) {
  die("No Hacking");
}
require_once (APP_ROOT . '/includes/os/class.parseProgs.inc.php');
class bsd_common {
  private $dmesg;
  private $parser;
  private $debug = debug;
  private $cpu_regexp1 = "";
  private $cpu_regexp2 = "";
  private $scsi_regexp1 = "";
  private $scsi_regexp2 = "";
  private $pci_regexp1 = "";
  private $pci_regexp2 = "";
  // Our constructor
  // this function is run on the initialization of this class
  public function __construct() {
    $this->parser = new Parser();
  }
  public function set_cpuregexp1($value) {
    $this->cpu_regexp = $value;
  }
  public function set_cpuregexp2($value) {
    $this->cpu_regexp2 = $value;
  }
  public function set_scsiregexp1($value) {
    $this->scsi_regexp1 = $value;
  }
  public function set_scsiregexp2($value) {
    $this->scsi_regexp2 = $value;
  }
  public function set_pciregexp1($value) {
    $this->pci_regexp1 = $value;
  }
  public function set_pciregexp2($value) {
    $this->pci_regexp2 = $value;
  }
  // read /var/run/dmesg.boot, but only if we haven't already.
  private function read_dmesg() {
    if (!$this->dmesg) {
      if (PHP_OS == "Darwin") {
        $this->dmesg = array();
      } else {
        if (rfts('/var/run/dmesg.boot', $buf)) {
          $parts = explode("rebooting", $buf);
          $this->dmesg = explode("\n", $parts[count($parts) -1]);
        } else {
          $this->dmesg = array();
        }
      }
    }
    return $this->dmesg;
  }
  // grabs a key from sysctl(8)
  protected function grab_key($key) {
    if (execute_program('sysctl', "-n $key", $buf, $this->debug)) {
      return $buf;
    } else {
      return '';
    }
  }
  // get our apache SERVER_NAME or vhost
  public function hostname() {
    if (!($result = getenv('SERVER_NAME'))) {
      $result = "N.A.";
    }
    return $result;
  }
  // get our canonical hostname
  public function chostname() {
    if (execute_program('hostname', '', $buf, $this->debug)) {
      return $buf;
    } else {
      return 'N.A.';
    }
  }
  // get the IP address of our canonical hostname
  public function ip_addr() {
    if (!($result = getenv('SERVER_ADDR'))) {
      $result = gethostbyname($this->chostname());
    }
    return $result;
  }
  public function vip_addr() {
    if (!($result = getenv('SERVER_ADDR'))) {
      $result = gethostbyname($this->chostname());
    }
    return $result;
  }
  public function kernel() {
    $s = $this->grab_key('kern.version');
    $a = explode(':', $s);
    return $a[0] . $a[1] . ':' . $a[2];
  }
  public function uptime() {
    $result = $this->get_sys_ticks();
    return $result;
  }
  public function users() {
    if (execute_program('who', '| wc -l', $buf, $this->debug)) {
      return $buf;
    } else {
      return 'N.A.';
    }
  }
  public function loadavg($bar = false) {
    $s = $this->grab_key('vm.loadavg');
    $s = ereg_replace('{ ', '', $s);
    $s = ereg_replace(' }', '', $s);
    $results['avg'] = explode(' ', $s);
    if ($bar) {
      if ($fd = $this->grab_key('kern.cp_time')) {
        // Find out the CPU load
        // user + sys = load
        // total = total
        preg_match($this->cpu_regexp2, $fd, $res);
        $load = $res[2]+$res[3]+$res[4]; // cpu.user + cpu.sys
        $total = $res[2]+$res[3]+$res[4]+$res[5]; // cpu.total
        // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
        sleep(1);
        $fd = $this->grab_key('kern.cp_time');
        preg_match($this->cpu_regexp2, $fd, $res);
        $load2 = $res[2]+$res[3]+$res[4];
        $total2 = $res[2]+$res[3]+$res[4]+$res[5];
        $results['cpupercent'] = (100*($load2-$load)) /($total2-$total);
      }
    }
    return $results;
  }
  public function cpu_info() {
    $results = array();
    $ar_buf = array();
    $results['model'] = $this->grab_key('hw.model');
    $results['cpus'] = $this->grab_key('hw.ncpu');
    for ($i = 0, $max = count($this->read_dmesg());$i < $max;$i++) {
      $buf = $this->dmesg[$i];
      if (preg_match("/$this->cpu_regexp/", $buf, $ar_buf)) {
        $results['cpuspeed'] = round($ar_buf[2]);
        break;
      }
    }
    return $results;
  }
  // get the scsi device information out of dmesg
  public function scsi() {
    $results = array();
    $ar_buf = array();
    for ($i = 0, $max = count($this->read_dmesg());$i < $max;$i++) {
      $buf = $this->dmesg[$i];
      if (preg_match("/$this->scsi_regexp1/", $buf, $ar_buf)) {
        $s = $ar_buf[1];
        $results[$s]['model'] = $ar_buf[2];
        $results[$s]['media'] = 'Hard Disk';
      } elseif (preg_match("/$this->scsi_regexp2/", $buf, $ar_buf)) {
        $s = $ar_buf[1];
        $results[$s]['capacity'] = $ar_buf[2]*2048*1.049;
      }
    }
    // return array_values(array_unique($results));
    // 1. more useful to have device names
    // 2. php 4.1.1 array_unique() deletes non-unique values.
    asort($results);
    return $results;
  }
  // get the pci device information out of dmesg
  public function pci() {
    $results = array();
    if (!(is_array($results = $this->parser->parse_lspci()) || is_array($results = $this->parser->parse_pciconf()))) {
      for ($i = 0, $s = 0;$i < count($this->read_dmesg());$i++) {
        $buf = $this->dmesg[$i];
        if (preg_match($this->pci_regexp1, $buf, $ar_buf)) {
          $results[$s++] = $ar_buf[1] . ": " . $ar_buf[2];
        } elseif (preg_match($this->pci_regexp2, $buf, $ar_buf)) {
          $results[$s++] = $ar_buf[1] . ": " . $ar_buf[2];
        }
      }
      asort($results);
    }
    return $results;
  }
  // get the ide device information out of dmesg
  public function ide() {
    $results = array();
    $s = 0;
    for ($i = 0, $max = count($this->read_dmesg());$i < $max;$i++) {
      $buf = $this->dmesg[$i];
      if (preg_match('/^(ad[0-9]+): (.*)MB <(.*)> (.*) (.*)/', $buf, $ar_buf)) {
        $s = $ar_buf[1];
        $results[$s]['model'] = $ar_buf[3];
        $results[$s]['media'] = 'Hard Disk';
        $results[$s]['capacity'] = $ar_buf[2]*1024;
      } elseif (preg_match('/^(acd[0-9]+): (.*) <(.*)> (.*)/', $buf, $ar_buf)) {
        $s = $ar_buf[1];
        $results[$s]['model'] = $ar_buf[3];
        $results[$s]['media'] = 'CD-ROM';
      }
    }
    // return array_values(array_unique($results));
    // 1. more useful to have device names
    // 2. php 4.1.1 array_unique() deletes non-unique values.
    asort($results);
    return $results;
  }
  // place holder function until we add acual usb detection
  public function usb() {
    return array();
  }
  public function memory() {
    $s = $this->grab_key('hw.physmem');
    if (PHP_OS == 'FreeBSD' || PHP_OS == 'OpenBSD') {
      // vmstat on fbsd 4.4 or greater outputs kbytes not hw.pagesize
      // I should probably add some version checking here, but for now
      // we only support fbsd 4.4
      $pagesize = 1024;
    } else {
      $pagesize = $this->grab_key('hw.pagesize');
    }
    $results['ram'] = array();
    if (!execute_program('vmstat', '', $pstat, $this->debug)) {
      $pstat = '';
    }
    $lines = explode("\n", $pstat);
    for ($i = 0, $max = sizeof($lines);$i < $max;$i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i], 19);
      if ($i == 2) {
        if (PHP_OS == 'NetBSD') {
          $results['ram']['free'] = $ar_buf[5];
        } else {
          $results['ram']['free'] = $ar_buf[5]*$pagesize/1024;
        }
      }
    }
    $results['ram']['total'] = $s/1024;
    $results['ram']['shared'] = 0;
    $results['ram']['buffers'] = 0;
    $results['ram']['used'] = $results['ram']['total']-$results['ram']['free'];
    $results['ram']['cached'] = 0;
    $results['ram']['percent'] = round(($results['ram']['used']*100) /$results['ram']['total']);
    if (PHP_OS == 'OpenBSD' || PHP_OS == 'NetBSD') {
      if (!execute_program('swapctl', '-l -k', $pstat, $this->debug)) {
        $pstat = '';
      }
    } else {
      if (!execute_program('swapinfo', '-k', $pstat, $this->debug)) {
        $pstat = '';
      }
    }
    $lines = explode("\n", $pstat);
    $results['swap']['total'] = 0;
    $results['swap']['used'] = 0;
    $results['swap']['free'] = 0;
    $results['swap']['percent'] = 0;
    for ($i = 1, $max = sizeof($lines);$i < $max;$i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i], 6);
      if ($ar_buf[0] != 'Total') {
        $results['swap']['total'] = $results['swap']['total']+$ar_buf[1];
        $results['swap']['used'] = $results['swap']['used']+$ar_buf[2];
        $results['swap']['free'] = $results['swap']['free']+$ar_buf[3];
        $results['devswap'][$i-1] = array();
        $results['devswap'][$i-1]['dev'] = $ar_buf[0];
        $results['devswap'][$i-1]['total'] = $ar_buf[1];
        $results['devswap'][$i-1]['used'] = $ar_buf[2];
        $results['devswap'][$i-1]['free'] = ($results['devswap'][$i-1]['total']-$results['devswap'][$i-1]['used']);
        $results['devswap'][$i-1]['percent'] = $ar_buf[2] > 0 ? round(($ar_buf[2]*100) /$ar_buf[1]) : 0;
      }
    }
    if (($i-1) > 0) {
      $results['swap']['percent'] = ceil(($results['swap']['used']*100) /(($results['swap']['total'] <= 0) ? 1 : $results['swap']['total']));
    }
    if (is_callable(array('sysinfo', 'memory_additional'))) {
      $results = $this->memory_additional($results);
    }
    return $results;
  }
  public function filesystems() {
    return $this->parser->parse_filesystems();
  }
  public function distro() {
    if (execute_program('uname', '-s', $result, $this->debug)) {
      return $result;
    } else {
      return 'N.A.';
    }
  }
}
?>
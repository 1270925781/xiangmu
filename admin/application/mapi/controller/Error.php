<?php
namespace app\mapi\controller;

class Error extends Common
{
	public function index()
	{
		exit(json_encode(-6));
	}
}
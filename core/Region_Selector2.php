<?php

class Region_Selector
{
	protected $region_auto_select = array();
	protected $regional_center = array();
	protected $def_city = '';

	public function __construct( $def_city = '')
	{
		$this->def_city = $def_city;
		
		$this->regional_center[Core::ROW_TYPE_REALTY_KIEV] = '����';
		$this->region_auto_select[Core::ROW_TYPE_REALTY_KIEV] = array(
			'�������������� ������',
			'���������� �-��',
			'���������� �-�',
			'�����������',
			'���������',
			'����� �������',
			'��������',
			'��������',
			'���������',
			'���������',
			'������',
			'�������',
			'����',
			'���������',
			'������',
			'�������',
			'���������',
			'������',
			'�������',
			'��������',
			'�����������',
			'�������',
			'���������',
			'������',
			'���������-�����������',
			'�������',
			'��������',
			'������',
			'������',
			//'��������',
			'������',
			'������',
			'��������',
			'����',
			'���������',
			'��������',
			'���������',
			'��������',
			'������',

			// ����
			'���������',
			'������� ����',
			'���������',
			'�������',
			'��������',
			'�������',
			'������������',
			'�����������',
			'��������',
			'������ �������',
			'��������',
			'����������',
			'��������',
			'����-�����������',
			'����-�����������',
			'�������',
			'�����',
			'�����',
			'�����',
			'�������',
			'���������',
			'����������',
			'�����',
			'���������',
			'��������',
			'����������',
			'�������',
			'���������',
			'�����',
			'������ �������',
			'����������',
			'���������',
			'����� ��������',
			'�������',
			'����������',
			'������',
			'�������',
			'�������',
			'�����',
			'�����',
			'�����',
			'����',
			'��������',
			'�����-���������',
			'������',
			'�������',
			'�����',
			'������ ��������',
			'�����',
			'���������',
			'�������',
			'�����������',
			'��������',
			'���������',
			'���������',
			'��������',
			'�����������',
			'���������������',
			'����������',
			'����������',
			'����������',
			'������� �������',
			'��������',
			'������������',
			'�������',
			'�������',
			'����������',
			'�������� ������',
			'���������',
			'�������',
			'��������',
			'�������',
			'���������',
			'�������',
			'����������',
			'������',
			'���������� �������',
			'�������',
			'����� ���������',
			'����� ��������',
			'����� �������',
			'����� �������������',
			'����� ������',
			'�����',
			'����������',
			'����������',
			'����� ������',
			'�������',
			'���������',
			'��������',
			'������ ������',
			'������',
			'���������',
			'�������',
			'���������',
			'������',
			'�������',
			'����������',
			'�������������',
			'���������',
			'���������',
			'����������',
			'������� ������',
			'����������',
			'�����������',
			'����������',
			'������',
			'����',
			'������',
			'������',
			'��������',
			'��������',
			'�������',
			'�����',
			'��������',
			'�������',
			'�������',
			'������� ���',
			'��������',
			'����� ������',
			'����� ������',
			'��������',
			'���������',
			'��������',
			'���������',
			'����� �������������',
			'���',
			'�������',
			'�����',
			'���������',
			'���������',
			'���������',
			'�������',
			'������',
			'�����',
			'�������',
			'���������',
			'�������',
			'��������',
			'�������',
			'�����������',
			'������',
			'����',
			'����',
			'����������',
			'������',
			'��������',
			'��������',
			'������ ����',
			'����',
			'�����',
			'��������',
			'���������',
			'�������',
			'���������',
			'��������',
			'����',
			'������',
			'���������',
			'����������',
			'���������',
			'���������',
			'�������������',
			'������',
			'������',
			'����������',
			'��������',
			'����������',
			'����������',
			'��������',
			'���������',
			'����������',
			'�������',
			'����������',
			'�������',
			'��������',
			'������',
			'�������',
			'���������',
			'������',
			'�������',
			'�����',
			'������',
			'����������',
			'�����',
			'���������',
			'�������',
			'���������',
			'���������',
			'����',
			'����',
			'������� ������',
			'����������������',
			'�����������',
			'����������',
			'����������',
			'��������',
			'�������',
			'��������',
			'��������',
			'�������� ������',
			'���������',
			'���������',
			'����������',
			'���������',
			'�����������',
			'�������',
			'���������',
			'����� ����������',
			'����� ������',
			'����� �������',
			'���������',
			'����������',
			'����� ������������',
			'����� ����',
			'��������',
			'������',
			'������������',
			'�������',
			'����',
			'��������',
			'�������',
			'����������',
			'�����������',
			'������',
			'�������',
			'�������',
			'���������',
			'��������',
			'��������',
			'��������',
			'�������',
			'��������',
			'���������',
			'��������',
			'���',
			'���������',
			'���������',
			'���������',
			'������� �������',
			'�������',
			'�����',
			'������ �������',
			'��������',
			'��������',
			'�������',
			'��������',
			'����������',
			'����������',
			'����c���',
			'��������',
			'���������',
			'�������',
			'���������',
			'�����',
			'���������',
			'���������',
			'���������',
			'����������',
			'��������',
			'�������',
			'���������',
			'������� �����',
			'������� �������',
			'��������',
			'�������',
			'������',
			'�����',
			'�������',
			'�������',
			'����������',
			'�������',
			'����������� ����',
			'��������',
			'����� �������',
			'����������',
			'�������',
			'�������',
			'������������',
			'��������',
			'����������',
			'����������',
			'�����������',
			'���������',
			'����� �������',
			'�������',
			'��������',
			'���������',
			'�������',
			'�����',
			'�����-��������',
			'�����������',
			'�������',
			'���������',
			'��������',
			'��������',
			'�������',
			'���������',
			'�����',
			'������������',
			'������ ���',
			'����',
			'������',
			'���������',
			'�����������',
			'���������',
			'���������',
			'����������',
			'������� �������������',
			'������� �������',
			'���������',
			'��������',
			'�������',
			'���������',
			'��������',
			'������',
			'���������',
			//'����',
			'����������',
			'��������',
			'����������',
			'��������',
			'���������',
			'�������',
			'�������',
			'������',
			'����������',
			'������',
			'��������',
			'�������',
			'���������',
			'�������',
			'����� �������������',
			'����� �������',
			'����� �������',
			'����������',
			'������',
			'����������',
			'������ ����',
			'��������',
			'����������',
			'��������',
			'������',
			'������',
			'�������',
			'���������',
			'��������',
			'������',
			'���������',
			'����������',
			'���������',
			'���������',
			'���������',
			'������-�������',
			'����������',
			'�����',
			'������� ������',
			'���������',
			'�����������',
			'���������',
			'�������',
			'���������',
			'��������',
			'���������',
			'��������',
			'�������',
			'�������',
			'��������',
			'��������',
			'�������',
			'���������',
			'��������',
			'��������',
			'�������',
			'�������',
			'�������',
			'���������',
			'������',
			'�������',
			'����������',
			'���������',
			'�����',
			'�����������',
			'����������',
			'�������',
			'������',
			'�������',
			'�����',
			'������',
			'���������',
			'������',
			'���������',
			'��������',
			'��������',
			'�����',
			'��������',
			'���������',
			'�������',
			'����',
			'���������',
			'���������',
			'�������',
			'���������',
			'��������',
			'��������',
			'�������',
			'���������',
			'����������',
			'�������',
			'������������ �������',
			'���������',
			'��������',
			'����������',
			'������',
			'�����',
			'������ ������',
			'�����������',
			'��������',
			'���������',
			'���������',
			'������',
			'�������',
			'������������',
			'���������',
			'����� ��',
			'�������',
			'��������� ������',
			'��������',
			'�������',
			'��������',
			'���������',
			'������',
			'����',
			'������� ���������',
			'������� ��������',
			'������� ���',
			'������������',
			'�������',
			'����������',
			'�������',
			'��������',
			'���������',
			'�������',
			'������',
			'������ �����',
			'��������',
			'����',
			'����������',
			'����������',
			'����� ���������',
			'����� ��������',
			'���������������',
			'����������',
			'�������',
			'������',
			'�����',
			'������',
			'�����������',
			'����������',
			'������',
			'������',
			'��������',
			'��������',
			'�������� ���',
			'������',
			'�������',
			'����������',
			'����������',
			'������� ��������',
			'��������',
			'���������',
			'����-����������',
			'�����',
			'������� ���',
			'�������',
			'������',
			'���',
			'�������',
			'�������-���������',
			'������',
			'���������',
			'��������',
			'���������',
			'������',
			'���������-��������',
			'�������',
			'��������� ���',
			'�������',
			'������� ���',
			'������� ���',
			'�����',
			'�������',
			'����������',
			'�������',
			'��������',
			'�����',
			'�����������',
			'������',
			'���������',
			'����� ����',
			'����� ������',
			'����� �������',
			'���������',
			'����� �������',
			//'�����',
			'��������',
			'��������',
			'����������',
			'�����������',
			'�������',
			'�����',
			'���������',
			'������',
			'������ ����',
			'��������',
			'��������',
			'���������',
			'�������',
			'�������',
			'������',
			'�������',
			'�����������',
			'�����������',
			'���������',
			'�������',
			'������� ��������',
			'������� ��������',
			'������� ����������',
			'�������',
			'��������',
			'���������',
			'��������� �����',
			'�������',
			'�������',
			'��������',
			'���������',
			'����������',
			'����������',
			'�������',
			'������������',
			'��������',
			'������',
			'�������',
			'��������',
			'��������',
			'������� ���',
			'����������',
			'���������',
			'�����',
			'���������',
			'������',
			'���������',
			'������� ����',
			'������',
			'��������',
			'����������',
			'���������� ������',
			'�������',
			'��������',
			'������� ������',
			'�������',
			'�������',
			'����� ��������',
			'����� ����������',
			'����������',
			'����������',
			'�������',
			'���������� ����������',
			'�������',
			//'��������',
			'�������',
			'���������',
			'�������',
			'������',
			'���������',
			'��������',
			'���������',
			'���������',
			'�������',
			'���������',
			'�������',
			'���������',
			'����������',
			'���������',
			'����������� ����������',
			'���������',
			'������',
			'��������',
			'�����������',
			'����',
			'������',
			'����� �������',
			'��������',
			'������� ������',
			'�������',
			'��������',
			'������������',
			'������� ���������',
			'����',
			'������',
			'��������',
			'��������',
			'������',
			'������',
			'��������',
			'���������',
			'������',
			'��������',
			'��������',
			'�������',
			'����������',
			'���������',
			'��������',
			'����� ���������',
			'����� ��������',
			'����� �������',
			'������',
			'�������',
			'����������',
			'�������',
			'�������',
			'���������� �������',
			'��������',
			'��������',
			'��������',
			'�������',
			'��������',
			'��������',
			'����������',
			'������',
			'��������',
			'����������',
			'������',
			'��������',
			'���������',
			'��������',
			'���������',
			'������',
			'����',
			'��������',
			'������',
			'�����',
			'�������',
			'����������',
			'������',
			'��������',
			'������',
			'��������',
			'�������',
			'���������',
			'���������',
			'������',
			'������',
			'������������',
			'������ ����',
			'��������',
			'���������',
			'�����������',
			'�������',
			'�����',
			'�������',
			'��������',
			'���������',
			'������',
			'��������������',
			'����������',
			'���������',
			'��������',
			'�������',
			'�������',
			'������',
			'���������',
			'�����',
			'���������',
			'��������',
			'����',
			'���������',
			'������',
			'�����������',
			'��������',
			'�����������',
			'����������',
			'��������',
			'�����',
			'���������',
			'���������������',
			'������',
			'��������',
			'������',
			'����������',
			'����� ���������',
			'��������',
			'���������',
			'�����',
			'����������',
			'��������',
			'��������',
			'��������',
			'�������',
			'�������',
			'�������',
			'���������',
			'�������',
			'��������',
			'���������',
			'���������',
			'��������� ������',
			'�������',
			'��������',
			'�������',
			'��������',
			'���������',
			'������',
			'����������',
			'���������',
			'������� ����������',
			'�������',
			'�������',
			'����������',
			'����������',
			'���������',
			'����������',
			'���������',
			'������',
			'�������',
			'��������',
			'����������',
			'��������',
			'�����',
			'������',
			'�������',
			'������� ��������',
			'������� ������',
			'�������',
			'����',
			'�����',
			'�������',
			'���������',
			'����� ��������',
			'����� �������',
			'����� ����������',
			'���������',
			'�������',
			'����� ���������',
			'������',
			'��������',
			'�����������',
			'����� ������',
			'�����',
			'��������',
			'��������',
			'������',
			'���������',
			'������ ���������',
			'������',
			'���������',
			'�������',
			'��������',
			'��������',
			'�������',
			'����������',
			'����������',
			'��������',
			'�������',
			'��������',
			'������������',
			'���������',
			'����',
			'��������',
			'��������-���������',
			'��������-��������������',
			'������',
			'���������',
			'������',
			'����������',
			'��������',
			'�������',
			'�������',
			'��������',
			'�������',
			'������',
			'�����������',
			'��������',
			'����������',
			'����������',
			'���� �����',
			'��������',
			'����������',
			'����������',
			'�����',
			'�����',
			'�������',
			'��������',
			'����� ����',
			'��������',
			'���������',
			'������',
			'���������',
			'����������',
			'�����������',
			'�������',
			'������',
			'������',
			'����',
			'����-��������',
			'��������',
			'�������',
			'������',
			'��������',
			'���������',
			'��������',
			'�������',
			'���������',
			'���������',
			'��������',
			'�������',
			'�������',
			'�������',
			'�����������',
			'�������',
			'�����������',
			'���������',
			'�������',
			'����������',
			'�������',
			'������',
			'����',
			'�������',
			'���������',
			'���������',
			'����',
			'��������',
			'����������-���������',
			'�����',
			'����',
			'������',
			'�������',
			'����',
			'��������� ',
			'����������',
			//'��������������� ����������',
			'��������',
			///'���������� ����������',
			//'�������',
			'���������',
			'��������',
			'���������',
			'�����',
			'�����',
			'������',
			'�����',
			'�������� �����',
			'����������',
			'�������',
			'������',
			'����� �����',
			'������',
			'�������',
			'������',
			'����-�������������',
			'�������',
			'����������',
			'����������',
			'�������������',
			'�����',
			'�������',
			'���������',
			'�������',
			'������',
			'����������',
			'�������',
			'��������',
			'�������',
			'���������',
			'��������',
			'�����',
			'�������',
			'�������',
			'����������',
			'������',
			'���������',
			'������������',
			'�������',
			'��������',
			'����������',
			'������',
			'��������',
			'��������',
			'�������',
			'����������',
			'���������',
			'����� ���������',
			'������',
			'������ �����',
			'�������',
			'����� ����������',
			'����� ������',
			'���������',
			'�����',
			'����������',
			'������',
			'������',
			'�����',
			'����������',
			'����������',
			'���������',
			'�����������',
			'����������',
			'������',
			'��������',
			'�������� �������',
			'����������',
			'��������',
			'�����-����������',
			'�����-�����������',
			'�����-��������',
			'�����-����������',
			'������',
			'����������',
			'�������-���������',
			'��������',
			'���������',
			'����������',
			'���������',
			'������ ������',
			'����������',
			'�����������',
			'������',
			'����������',
			'�����������',
			'���������',
			'��������',
			'����������',
			'������',
			'���������',
			'��������',
			'����������',
			'�����',
			'�������',
			'�������',
			'������',
			'����������',
			'������� �������',
			'�������',
			'�������',
			'���������',
			'���������',
			'�������',
			'��������',
			'�������',
			'�������',
			'����',
			'���������',
			'���������',
			'������',
			'�������',
			'����������',
			'���������',
			'���������',
			'�����',
			'�������',
			'����� ���������',
			'����������',
			'������',
			'������ ����',
			'��������',
			'������������',
			'��������',
			'���������',
			'����� ������',
			'�������',
			'�������',
			'��������',
			'�������',
			'�������',
			'�����',
			'�����',
			'�������',
			'���������',
			'���������',
			'���������',
			'������',
			'���������',
			'��������',
			'���������',
			'����������',
			'����������',
			'�����',
			'�����������',
			'����������',
			'���������',
			'����������',
			'����������',
			'�����������',
			'��������',
			'������������',
			'�������',
			'����������',
			'�������',
			'����������',
			'������������',
			'���������',
			'���������',
			'���������',
			'������� ��',
			'���������',
			'�����������',
			'�������',
			'�������',
			'��������',
			'���������',
			'����������',
			'��������',
			'�����������',
			'����������',
			'���������',
			'��������',
			'����������',
			'�������',
			'����������',
			'���������',
			'��������',
			'�������� �������',
			'����������',
			'����������',
			'����������',
			'������',
			'������� ��������',
			'������� ������',
			'�������',
			'��������',
			'���������',
			'������� �����������',
			'������� ������',
			'������',
			'�����',
			'�������',
			'������',
			'�����������',
			'���������',
			'���������',
			'���������',
			'���������',
			'������������',
			'�������',
			'��������',
			'�������',
			'�������',
			'���������',
			'��������',
			'������� �����������',
			'����� ������',
			'����� ��������',
			'��������������',
			'������',
			'������������� ��������',
			'��������',
			'���������� ����',
			'�����������',
			'������',
			'���������',
			'�����������',
			'�����������',
			'��������',
			'������',
			'��������',
			'���������',
			'���������',
			'���������',
			'�������',
			'������',
			'��������',
			'���������',
			'��������',
			'��������',
			'����',
			'���� �����������',
			'����-���������',
			'�������',
			'���������',
			'�������',
			'����������',
			'�������',
			'��������',
			'������� ������',
			'���������',
			'������� ������',
			'���������',
			'��������',
			'�����������',
			'����������',
			'����������',
			'��������',
			'��������',
			'����������',
			'������������',
			'�������',
			'�������',
			'������ ��������',
			'�������',
			'��������',
			'���������',
			'�������',
			'���������',
			'������',
			'��������',
			'�������',
			'�������',
			'��������',
			'��������',
			'����������',
			'���������',
			'��������',
			'��������',
			'������',
			'����������',
			'�����',
			'�����',
			'����������',
			'��������',
			'��������',
			'��������',
			'����� ������',
			'�������',
			'��������',
			'���������',
			'�������',
			'��������',
			'��������',
			'��������',
			'��������',
			'�������',
			'���������',
			'���������',
			'������',
			'��������',
			'�������',
			'���������',
			'������� ��������',
			'�������',
			'�������',
			'�������',
			'�������������',
			'��������',
			'������',
			'��������',
			'�������',
			'������',
			'���������',
			'�������',
			'��������',
			'������',
			'�������',
			'���������',
			'��������',
			'������',
			'�������',
			'������',
			'������',
			'�����',
			'�������',
			'����� ��������',
			'����������',
			'������������',
			'���������-�����������',
			'�������������',
			'����� ������',
			'��������',
			'������-�������',
			'������-�������',
			'������-������',
			'�������',
			'�������',
			'���������',
			'���������',
			'��������',
			'������� ������',
			'�������',
			'��������',
			'��������',
			'���������',
			'������',
			'���������',
			'���������',
			'���������',
			'�����',
			'�����',
			'�������',
			'�������',
			'����������',
			'���������',
			'������',
			'���������',
		);
	}

	public function find($text)
	{
		foreach($this->region_auto_select as $region_id => $city_list) {
			if ($this->def_city && $this->regional_center[$region_id] != $this->def_city) {
				continue;
			}
			foreach ($city_list as $city) {
				if ($text == $city || preg_match('#([ !?,:\)\.-])+(����|����|�\.|����)\s*(' . $city . ')+([ !?,:\)\.-])+#si', $text, $matches)) {

				//if (strpos($text, $city) !== false) {
					return array(
						'row_type' => $region_id,
						'city_name' => $city,
					);
				}
				if ($text == $city || preg_match('#([ !?,:\)\.-])+(' . $city . '�'. ')+([ !?,:\)\.-])+#si', $text, $matches)) {
				//if (strpos($text, $city) !== false) {
					return array(
						'row_type' => $region_id,
						'city_name' => $city,
					);
				}
			}
		}
		return false;
	}
}
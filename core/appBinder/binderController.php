<?php
include('binderFunctions.php');
include('templateConverter.php');
include('modelCombiner.php');

class lsjs_binderController {
	const c_str_pathToCore = '..';
	
	const c_str_pathToModules = 'modules';
	const c_str_pathToModels = 'models';
	const c_str_pathToStyles = 'styles';
	const c_str_pathToTemplates = 'templates';
	const c_str_pathToMasterStyles = 'styles';
	
	const c_str_viewFileName = 'view.js';
	const c_str_controllerFileName = 'controller.js';
	
	const c_str_appFileName = 'app.js';
	
	const c_str_lsjsFileName = 'lsjs.js';
	const c_str_lsjsTemplateHandlerFileName = 'lsjs_templateHandler.js';
	const c_str_lsVersionFileName = 'ls_version.txt';
	
	const c_str_pathToAppBinderBaseFiles = 'baseFiles';
	const c_str_mainContainerBasisFileName = 'mainContainer.js';
	const c_str_templateBasisFileName = 'templateBasis.js';
	const c_str_modelBasisFileName = 'modelBasis.js';
	const c_str_moduleBasisFileName = 'moduleBasis.js';
	
	const c_str_templatesPath = 'resources/lsjs/app/modules/%s/templates';
	
	protected $str_pathToApp = '../../app';
	
	protected $str_useBlackOrWhitelist = '';
	protected $arr_moduleBlackOrWhitelist = array();
	
	protected $bln_includeCore = true;
	protected $bln_includeCoreModules = true;
	protected $bln_includeAppModules = true;
	protected $bln_includeApp = true;
	protected $bln_includeMasterStyleFiles = true;


	protected $arr_files = array();
	protected $arr_moduleStructure = array();
	
	protected $str_output = '';
	
	public function __construct() {
		$this->processGetParameters();
		$GLOBALS['lsjs']['appBinder']['str_pathToApp'] = $this->str_pathToApp;
		$this->readAllFiles();
	}
	
	public function outputJS() {
		header("Content-Type: application/javascript");
		
		$this->str_output = lsjsBinder_file_get_contents(self::c_str_pathToAppBinderBaseFiles.'/'.self::c_str_mainContainerBasisFileName);
		$this->str_output = preg_replace('/__ls_version__/', (!$this->bln_includeCore ? '' : '/* '.$this->file_get_contents_envelope($this->arr_files['mainCoreFiles']['ls_version']).' */'), $this->str_output);
		$this->str_output = preg_replace('/__lsjs__/', (!$this->bln_includeCore ? '' : $this->file_get_contents_envelope($this->arr_files['mainCoreFiles']['lsjs'])), $this->str_output);
		$this->str_output = preg_replace('/__lsjs_templateHandler__/', (!$this->bln_includeCore ? '' : $this->file_get_contents_envelope($this->arr_files['mainCoreFiles']['lsjs_templateHandler'])), $this->str_output);
		$this->str_output = preg_replace('/__app__/', (!$this->bln_includeApp ? '' : $this->file_get_contents_envelope($this->arr_files['mainAppFile'])), $this->str_output);
				
		$this->generateModuleOutput('core');
		$this->generateModuleOutput('app');
		
		echo $this->str_output;
		exit;
	}
	
	public function outputCSS() {
		header("Content-Type: text/css");
		
		if ($this->bln_includeMasterStyleFiles) {
			foreach ($this->arr_files['masterStyleFiles'] as $str_filePath) {
				if (!file_exists($str_filePath)) {
					continue;
				}
				
				$this->str_output .= "\r\n\r\n\r\n\r\n/*\r\n";
				$this->str_output .= 'MASTER STYLE FILE "'.pathinfo($str_filePath, PATHINFO_BASENAME).'"';
				$this->str_output .= "\r\n*/\r\n\r\n";
				$this->str_output .= $this->file_get_contents_envelope($str_filePath);
			}
		}
		
		$this->addModuleStylesheetsToOutput('core');
		$this->addModuleStylesheetsToOutput('app');
		
		echo $this->str_output;
		exit;
	}
	
	protected function addModuleStylesheetsToOutput($str_what) {
		if (!$str_what) {
			throw new Exception(__METHOD__.': $str_what not given');
		}
		
		if (!in_array($str_what, array('core', 'app'))) {
			throw new Exception(__METHOD__.': $str_what has unsupported value');
		}
		
		if (isset($this->arr_files[$str_what.'ModuleFiles']) && is_array($this->arr_files[$str_what.'ModuleFiles'])) {
			foreach ($this->arr_files[$str_what.'ModuleFiles'] as $str_moduleName => $arr_moduleFiles) {
				sort($arr_moduleFiles['styleFiles']);
				foreach ($arr_moduleFiles['styleFiles'] as $str_filePath) {
					if (!file_exists($str_filePath)) {
						return;
					}

					$this->str_output .= "\r\n\r\n\r\n\r\n/*\r\n";
					$this->str_output .= 'MODULE "'.$str_moduleName.'"';
					$this->str_output .= "\r\n";
					$this->str_output .= 'STYLE FILE "'.pathinfo($str_filePath, PATHINFO_BASENAME).'"';
					$this->str_output .= "\r\n*/\r\n\r\n";
					$this->str_output .= $this->file_get_contents_envelope($str_filePath);
				}
			}
		}
	}
	
	protected function readAllFiles() {
		if (!file_exists($this->str_pathToApp)) {
			throw new Exception(__METHOD__.': app folder "'.$this->str_pathToApp.'" does not exist.');
		}
		
		if ($this->bln_includeCore) {
			$this->arr_files['mainCoreFiles'] = array(
				'lsjs' => self::c_str_pathToCore.'/'.self::c_str_lsjsFileName,
				'lsjs_templateHandler' => self::c_str_pathToCore.'/'.self::c_str_lsjsTemplateHandlerFileName,
				'ls_version' => self::c_str_pathToCore.'/'.self::c_str_lsVersionFileName
			);
		}
		
		if ($this->bln_includeApp) {
			$this->arr_files['mainAppFile'] = $this->str_pathToApp.'/'.self::c_str_appFileName;
		}
		
		if ($this->bln_includeCoreModules) {
			$this->arr_files['coreModuleFiles'] = $this->readModules(self::c_str_pathToCore.'/'.self::c_str_pathToModules);
		}
		
		if ($this->bln_includeAppModules) {
			$this->arr_files['appModuleFiles'] = $this->readModules($this->str_pathToApp.'/'.self::c_str_pathToModules);
		}
		
		$this->arr_files['masterStyleFiles'] = $this->readFiles($this->str_pathToApp.'/'.self::c_str_pathToMasterStyles);
	}
	
	protected function readCssFiles() {
		$this->readAllFiles();
	}
	
	protected function readModules($str_pathToModules, $arr_modules = array(), &$arr_moduleStructure = null) {
		$bln_isRootCall = false;
		
		if ($arr_moduleStructure === null) {
			$arr_moduleStructure = &$this->arr_moduleStructure;
			$bln_isRootCall = true;
		}
		
		if (!$bln_isRootCall) {
			if ($this->str_useBlackOrWhitelist) {
				switch ($this->str_useBlackOrWhitelist) {
					case 'white':
						if (!in_array(md5($str_pathToModules), $this->arr_moduleBlackOrWhitelist)) {
							return $arr_modules;
						}
						break;
						
					case 'black':
						if (in_array(md5($str_pathToModules), $this->arr_moduleBlackOrWhitelist)) {
							return $arr_modules;
						}
						break;
				}
			}
		}

		
		if (!isset($arr_moduleStructure[$bln_isRootCall ? 'allModules' : $str_pathToModules])) {
			$arr_moduleStructure[$bln_isRootCall ? 'allModules' : $str_pathToModules] = array(
				'str_hash' => $bln_isRootCall ? '' : md5($str_pathToModules),
				'str_path' => $bln_isRootCall ? '' : $str_pathToModules,
				'str_moduleName' => basename($str_pathToModules),
				'arr_children' => array()
			);
		}
		
		foreach(lsjsBinder_scandir($str_pathToModules) as $str_filename) {
			if (
					$str_filename === '.' || $str_filename === '..'
				||	!is_dir($str_pathToModules.'/'.$str_filename)
			) {
				continue;
			}
						
			$str_moduleName = $str_filename;
						
			/*
			 * ->
			 * If a module folder doesn't contain a controller file, we assume that
			 * the folder is a module group folder and therefore we look for modules
			 * inside.
			 */
			if (!file_exists($str_pathToModules.'/'.$str_moduleName.'/'.self::c_str_controllerFileName)) {
				$arr_modules = $this->readModules($str_pathToModules.'/'.$str_moduleName, $arr_modules, $arr_moduleStructure[$bln_isRootCall ? 'allModules' : $str_pathToModules]['arr_children']);
				continue;
			}
			/*
			 * <-
			 */
			
			if ($this->str_useBlackOrWhitelist) {
				switch ($this->str_useBlackOrWhitelist) {
					case 'white':
						if (!in_array(md5($str_pathToModules.'/'.$str_moduleName), $this->arr_moduleBlackOrWhitelist)) {
							continue 2;
						}
						break;
						
					case 'black':
						if (in_array(md5($str_pathToModules.'/'.$str_moduleName), $this->arr_moduleBlackOrWhitelist)) {
							continue 2;
						}
						break;
				}
			}
			
			$arr_moduleStructure[$bln_isRootCall ? 'allModules' : $str_pathToModules]['arr_children'][$str_pathToModules.'/'.$str_moduleName] = array(
				'str_hash' => md5($str_pathToModules.'/'.$str_moduleName),
				'str_path' => $str_pathToModules.'/'.$str_moduleName,
				'str_moduleName' => $str_moduleName,
				'arr_children' => array()
			);
			
			$arr_modules[$str_moduleName] = $this->readModuleFolders($str_pathToModules.'/'.$str_moduleName);
		}
		return $arr_modules;
	}
	
	protected function readModuleFolders($str_modulePath) {
		return array(
			'viewFile' => $str_modulePath.'/'.self::c_str_viewFileName,
			'controllerFile' => $str_modulePath.'/'.self::c_str_controllerFileName,
			'modelPath' => $str_modulePath.'/'.self::c_str_pathToModels,
			'templatePath' => $str_modulePath.'/'.self::c_str_pathToTemplates,
			'styleFiles' => $this->readFiles($str_modulePath.'/'.self::c_str_pathToStyles)
		);
	}
	
	protected function readFiles($str_pathToFiles) {
		$arr_return = array();
		if (!file_exists($str_pathToFiles) || !is_dir($str_pathToFiles)) {
			return $arr_return;
		}
		
		foreach(scandir($str_pathToFiles) as $str_filename) {
			if (is_dir($str_pathToFiles.'/'.$str_filename)) {
				continue;
			}
			
			$arr_return[] = $str_pathToFiles.'/'.$str_filename;
		}
		
		return $arr_return;
	}
	
	protected function generateModelOutput($str_moduleName, $str_modelPath) {
		if (!file_exists($str_modelPath) || !is_dir($str_modelPath)) {
			return '';
		}
		
		try {
			$obj_modelCombiner = new modelCombiner(
				$str_moduleName,
				$str_modelPath,
				self::c_str_pathToAppBinderBaseFiles.'/'.self::c_str_modelBasisFileName
			);
			return $obj_modelCombiner->output();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	protected function generateTemplateOutput($str_moduleName, $str_templatePath) {
		if (!file_exists($str_templatePath) || !is_dir($str_templatePath)) {
			return '';
		}
		
		try {
			$obj_lsjs_templateConverter = new lsjs_templateConverter(
				$str_moduleName,
				$str_templatePath,
				self::c_str_pathToAppBinderBaseFiles.'/'.self::c_str_templateBasisFileName
			);
			return $obj_lsjs_templateConverter->output();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	protected function generateModuleOutput($str_what) {
		if (!$str_what) {
			throw new Exception(__METHOD__.': $str_what not given');
		}
		
		if (!in_array($str_what, array('core', 'app'))) {
			throw new Exception(__METHOD__.': $str_what has unsupported value');
		}
		
		$str_completeModuleOutput = '';
		if (isset($this->arr_files[$str_what.'ModuleFiles']) && is_array($this->arr_files[$str_what.'ModuleFiles'])) {
			foreach($this->arr_files[$str_what.'ModuleFiles'] as $str_moduleName => $arr_moduleFiles) {
				$str_moduleOutput = lsjsBinder_file_get_contents(self::c_str_pathToAppBinderBaseFiles.'/'.self::c_str_moduleBasisFileName);
				$str_moduleOutput = preg_replace('/__viewFile__/', $this->file_get_contents_envelope($arr_moduleFiles['viewFile']), $str_moduleOutput);
				$str_moduleOutput = preg_replace('/__controllerFile__/', $this->file_get_contents_envelope($arr_moduleFiles['controllerFile']), $str_moduleOutput);

				$str_tmpOutput = $this->generateModelOutput($str_moduleName, $arr_moduleFiles['modelPath']);
				$str_moduleOutput = preg_replace('/__modelFiles__/', $str_tmpOutput, $str_moduleOutput);

				$str_tmpOutput = $this->generateTemplateOutput($str_moduleName, $arr_moduleFiles['templatePath']);
				$str_moduleOutput = preg_replace('/__templateFiles__/', $str_tmpOutput, $str_moduleOutput);

				$str_completeModuleOutput .= "\r\n".preg_replace('/__moduleName__/', $str_moduleName, $str_moduleOutput);
			}
		}
		$this->str_output = preg_replace('/__'.$str_what.'_modules__/', $str_completeModuleOutput, $this->str_output);
	}
	
	/*
	 * The only purpose of this function is to return the file path as a string
	 * instead of the complete file content in test cases.
	 */
	protected function file_get_contents_envelope($str_filePath) {
		$bln_testMode = false;
		if ($bln_testMode) {
			if (!file_exists($str_filePath)) {
				return 'FILE DOES NOT EXIST: '.$str_filePath;
			}
			return $str_filePath;
		}
		
		if (!file_exists($str_filePath)) {
			return '';
		}
		return lsjsBinder_file_get_contents($str_filePath);
	}
	
	public function setModuleWhitelist($str_list) {
		$this->arr_moduleBlackOrWhitelist = array_map('trim', explode(',', $str_list));
		if (count($this->arr_moduleBlackOrWhitelist)) {
			$this->str_useBlackOrWhitelist = 'white';
		}
	}
	
	public function setModuleBlacklist($str_list) {
		$this->arr_moduleBlackOrWhitelist = array_map('trim', explode(',', $str_list));
		if (count($this->arr_moduleBlackOrWhitelist)) {
			$this->str_useBlackOrWhitelist = 'black';
		}
	}
	
	public function getModuleStructure() {
		return $this->arr_moduleStructure;
	}
	
	public function getFileList() {
		return $this->arr_files;
	}
	
	protected function processGetParameters() {
		if (isset($_GET['pathToApp']) && $_GET['pathToApp']) {
			/*
			 * Since passing a url as a get parameter can cause the request to be blocked when there are many "folder up" parts
			 * in the url (false positive for apache parent directory attack), we use a special keyword followed by a number
			 * (e.g. _dup7_) to name the number of "folder ups" and then translate it into the correct "../../../.." part.
			 */
			$this->str_pathToApp = preg_replace_callback(
				'/_dup([0-9]+?)_/',
				function($arr_matches) {
					$arr_dirUp = array();
					for ($i = 1; $i <= $arr_matches[1]; $i++) {
						$arr_dirUp[] = '..';
					}
					$str_dirUpPrefix = implode('/', $arr_dirUp);

					return $str_dirUpPrefix;
				},
				$_GET['pathToApp']
			);
		}
		
		if (isset($_GET['whitelist']) && $_GET['whitelist']) {
			$this->setModuleWhitelist($_GET['whitelist']);
		}
		
		if (isset($_GET['blacklist']) && $_GET['blacklist']) {
			$this->setModuleBlacklist($_GET['blacklist']);
		}
		
		if (isset($_GET['includeCore'])) {
			if ($_GET['includeCore'] == 'yes') {
				$this->bln_includeCore = true;
			} else {
				$this->bln_includeCore = false;
			}
		}
		
		if (isset($_GET['includeCoreModules'])) {
			if ($_GET['includeCoreModules'] == 'yes') {
				$this->bln_includeCoreModules = true;
			} else {
				$this->bln_includeCoreModules = false;
			}
		}
		
		if (isset($_GET['includeAppModules'])) {
			if ($_GET['includeAppModules'] == 'yes') {
				$this->bln_includeAppModules = true;
			} else {
				$this->bln_includeAppModules = false;
			}
		}
		
		if (isset($_GET['includeApp'])) {
			if ($_GET['includeApp'] == 'yes') {
				$this->bln_includeApp = true;
			} else {
				$this->bln_includeApp = false;
			}
		}
		
		if (isset($_GET['includeMasterStyleFiles'])) {
			if ($_GET['includeMasterStyleFiles'] == 'yes') {
				$this->bln_includeMasterStyleFiles = true;
			} else {
				$this->bln_includeMasterStyleFiles = false;
			}
		}
	}
}
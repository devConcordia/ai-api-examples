<?php namespace OpenAI;

use Exception;

///
const URL_API_CHAT = "https://api.openai.com/v1/chat/completions";

/** makeHttpRequest
 *	
 */
function makeHttpRequest( string $url, array $header, string $data = "" ) {
	
	$ch = curl_init();
	
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_HEADER, false );
	
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	if( $data != "" ) {
		
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	
	}
	
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	
	$output = curl_exec( $ch );
	
	curl_close( $ch );
	
	return $output;
	
};

/**	ChatCompletions
 *	
 *	Exemplo:
 *	
 *		$chatgpt = new OpenAI\ChatCompletions( YOUR_API_KEY );
 *		
 *		$chatgpt->append( "system", "Instruções para o comportamento do chat" );
 *		
 *		$onlyResponse = true;
 *		$output = $chatgpt->generate( $onlyResponse );
 *		
 *	Adicione a resposta do assistente para manter o contexto:
 *	
 *		$chatgpt->append( "assistant", $output );
 *	
 *	ou limpe a conversa para uma conversa sem contexto:
 *		
 *		$chatgpt->clear();
 *	
 */
class ChatCompletions {
	
	/// @ref https://platform.openai.com/docs/pricing
	public const GPT4 = "gpt-4o-mini-2024-07-18";
	
	/** 
	 *	
	 *	@ref https://platform.openai.com/docs/api-reference/chat/create
	 *	@ref https://platform.openai.com/docs/models/model-endpoint-compatibility
	 *	
	 *	@param {string} $apiKey
	 *	@param {string} $model		
	 */
	function __construct( string $apiKey, string $model = "" ) {
		
		if( empty($model) ) 
			$model = ChatCompletions::GPT4;
		
		///
		$this->apiKey = $apiKey;
		
		/// request setup
		/// @ref https://platform.openai.com/docs/api-reference/chat/create
		$this->model = $model;
		$this->temperature = 0;
		$this->messages = array();
		
		/// outras configurações
		/// The maximum number of tokens that can be generated in the chat completion.
		$this->max_tokens = null;
		
	}
	
	public function clear() {
		
		$this->messages = array();
		
	}
	
	/** append
	 *	
	 *	@param {string} $role		system | user | assistant
	 *	@param {string} $content
	 */
	public function append( string $role, string $content ) {
		
		if( $this->maxChars > 0 )
			$content = substr( $content, 0, $this->maxChars );
		
		$this->messages[] = array( "role"=> $role, "content"=> $content );
		
	}
	
	/** generate
	 *	
	 *	@param {bool} $responseOnly		Se true, irá devolver somente o texto gerado
	 *	@return {object} or {string}
	 */
	public function generate( bool $responseOnly = false ) {
		
		$header = array(
			"Content-Type: application/json",
			"Authorization: Bearer ". $this->apiKey
		);
		
		$data = array(
			"model" => $this->model,
			"temperature" => $this->temperature,
			"messages" => $this->messages
		);
		
		if( !is_null($this->max_tokens) ) 
			$data["max_tokens"] = $this->max_tokens;
		
		/// 
		$response = makeHttpRequest( URL_API_CHAT, $header, json_encode($data) );
		
		if( gettype($response) == "string" )
			$response = json_decode( $response );
		
		if( isset( $response->error ) )
			throw new Exception( $response->error->message, 1 );
		
		/// responde somente com a resposta
		if( $responseOnly ) {
			if( isset( $response->choices ) ) {
				
				$choice = end( $response->choices );
				
				if( $choice->finish_reason == "stop" ) {
					if( isset( $choice->message ) ) {
						
						$message = $choice->message;
						
						if( isset( $message->content ) )
							return $message->content;
						
					}
				}
			}
		}
		
		return $response;
		
	}
	
}

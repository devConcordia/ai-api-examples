import urllib.request
import urllib.error
import json


# https://groq.com/
GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions"
GROQ_API_KEY = "YOUR_KEY"



def postHttpRequest( url, input, token ):
    
    data = json.dumps( input ).encode('utf-8')
    
    headers = {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer '+ token,
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
    }
    
    req = urllib.request.Request(url, data=data, headers=headers, method='POST')
    
    try:
        with urllib.request.urlopen(req) as response:
            res = response.read().decode('utf-8')
            return json.loads( res )
    except urllib.error.HTTPError as e:
        print(f"Erro HTTP: {e.code} - {e.reason}")
        
        try:
            print(f"[HTTPError] Corpo da resposta de erro: "+ e.read().decode('utf-8'))
        except:
            print("[HTTPError] Não foi possível ler o corpo da resposta.")
        
    except urllib.error.URLError as e:
        print(f"Erro de URL: {e.reason}")
    return None

def groqChatCompletions( systemPrompt, clientQuestion ):
    
    data = {}
    
    # https://console.groq.com/docs/models
    #data["model"] = "gemma2-9b-it"
    data["model"] = "llama-3.3-70b-versatile"
    data["messages"] = [
        {
            "role": "system",
            "content": systemPrompt
        }, {
            "role": "user",
            "content": clientQuestion
        }
    ]
    
    return postHttpRequest( GROQ_API_URL, data, GROQ_API_KEY )

prompt = '''Instruções do chatbot'''


print('Olá, como posso ajudar?')

while True:
    user = input('Pergunta: ')
    res = groqChatCompletions( prompt, user )
    print( res['choices'][0]['message']['content'] )
    print('\n')

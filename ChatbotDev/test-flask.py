#!/usr/bin/env python
# coding: utf-8

# In[ ]:


# Importing flask module in the project is mandatory
# An object of Flask class is our WSGI application.
import json
import numpy as np
import keras
from keras_preprocessing.text import tokenizer_from_json
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences
from flask import Flask, render_template, request

 
'''    
# The route() function of the Flask class is a decorator, 
# which tells the application which URL should call 
# the associated function.
@app.route('/')
# ‘/’ URL is bound with hello_world() function.
def hello_world():
    return 'Hello World'
'''

intent_recognition_model = keras.models.load_model('intent_recognition_model.keras')

with open('tokenizer.json') as f:
    data = json.load(f)
    tokenizer = tokenizer_from_json(data)
    
#Parameters of Tokenizer
vocab_size = 1000
max_length = 50
trunc_type='post'
padding_type='post'
oov_tok = "<OOV>"

def getIntent(user_prompt):
    tokens = tokenizer.texts_to_sequences([user_prompt])
    tokens = pad_sequences(tokens, maxlen=max_length, padding=padding_type)
    probabilities = intent_recognition_model.predict(tokens)
    choice = np.random.choice([0,1,2,3])
    predicted = np.argsort(probabilities)[0][-choice]
    return predicted

#0: Contact Human Agent - Ask for live human agent
#1: Medicine Suggestion Query - Ask for suggestion of OTC medicines based from symptoms or generic drug name (ex. do you have anything for flu?; do you have vitamin D supplements?). 
#2: Medicine Dosage Query - Ask for dosage directions
#3: Medicine Availability Query - 

def GenerateBotResponse(intent):
    if (intent == 0):
        return "We will get a human agent to talk with you as soon as possible."
    elif (intent == 1):
        return "Here is the medicine you asked for."
    elif (intent == 2):
        return "Here are the instructions for taking your medicine:"
    elif (intent == 3):
        return "Here is the availability of the medicine you asked for."
    else:
        return "I'm sorry, I don't understand."
    

# Flask constructor takes the name of 
# current module (__name__) as argument.
app = Flask(__name__, template_folder='chat_templates')

@app.route("/index")
def index():
    return render_template("chat.html")
    
@app.route("/chatbot", methods=["GET", "POST"])
def chatbot():
    if request.method == "POST":
        user_input = request.form.get("user_input")
        chatbot_response = GenerateBotResponse(getIntent(user_input))
        return chatbot_response
    else:
        user_input = request.form.get("user_input")
        chatbot_response = GenerateBotResponse(getIntent(user_input))
        return chatbot_response
 
# main driver function
if __name__ == '__main__':
 
    # run() method of Flask class runs the application 
    # on the local development server.
    app.run(debug=True)


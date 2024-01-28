#!/usr/bin/env python
# coding: utf-8

# In[ ]:

import json
import numpy as np
import tensorflow as tf
import keras
from keras_preprocessing.text import tokenizer_from_json
from keras.models import Model, Sequential #This works apparently and I have no idea why
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.preprocessing.sequence import pad_sequences

with open('tokenizer.json') as f:
    data = json.load(f)
    tokenizer = tokenizer_from_json(data)
    
    
#Parameters of Tokenizer
vocab_size = 1000
max_length = 50
trunc_type='post'
padding_type='post'
oov_tok = "<OOV>"

intent_recognition_model = keras.models.load_model('intent_recognition_model.keras')
probability_model = Sequential([intent_recognition_model, tf.keras.layers.Softmax()])

def getIntent(user_prompt):
    #print(f"User Prompt: {user_prompt}")
    tokens = tokenizer.texts_to_sequences([user_prompt])
    #print(f"Tokens: {tokens}")
    tokens = pad_sequences(tokens, maxlen=max_length, padding=padding_type)
    probabilities = probability_model.predict(tokens)
    #print(f"Probabilities: {probabilities}")
    predicted_label = np.argmax(probabilities)
    #print(f"Tokens: {tokens}")
    return predicted_label

#0: Contact Human Agent - Ask for live human agent
#1: Medicine Suggestion Query - Ask for suggestion of OTC medicines based from symptoms or generic drug name (ex. do you have anything for flu?; do you have vitamin D supplements?). 
#2: Medicine Dosage Query - Ask for dosage directions
#3: Medicine Availability Query - 

def GenerateBotResponse(prompt):
    intent = getIntent(prompt)
    if (intent == 0):
        chatbot_response = "We will get a human agent to talk with you as soon as possible."
    elif (intent == 1):
        chatbot_response = "Here is the medicine you asked for."
    elif (intent == 2):
        chatbot_response = "Here are the instructions for taking your medicine:"
    elif (intent == 3):
        chatbot_response = "Here is the availability of the medicine you asked for."
    else:
        return "I'm sorry, I don't understand."
    message = {"answer": chatbot_response}


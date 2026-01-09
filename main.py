import math
from collections import defaultdict
from nltk.corpus import stopwords
from nltk.tokenize import word_tokenize, sent_tokenize

# ======================
# Sample Input Text
# ======================
text = """
Regresi linear adalah salah satu algoritma dasar dalam machine learning yang bekerja dengan memodelkan hubungan linear antara variabel input (independen) dan variabel output (dependen). Model ini berusaha menemukan garis lurus terbaik yang dapat meminimalkan jarak antara prediksi model dengan data aktual, yang dikenal sebagai "line of best fit". 
Cara kerja regresi linear dimulai dengan menentukan persamaan garis lurus y = mx + b, di mana y adalah variabel target yang ingin diprediksi, x adalah variabel input, m adalah slope (kemiringan garis), dan b adalah intercept (titik potong dengan sumbu y). Model akan mencari nilai m dan b yang optimal menggunakan metode yang disebut Ordinary Least Squares (OLS), yang bekerja dengan meminimalkan jumlah kuadrat dari selisih antara nilai prediksi dan nilai aktual.
Dalam prosesnya, model regresi linear menggunakan gradient descent, sebuah algoritma optimasi yang secara iteratif menyesuaikan nilai parameter (m dan b) untuk mengurangi error prediksi. Pada setiap iterasi, model menghitung error prediksi, kemudian mengupdate parameter dengan menggerakkannya ke arah yang menurunkan error. Proses ini berlanjut hingga model mencapai konvergensi, yaitu ketika perubahan parameter sudah sangat kecil atau jumlah iterasi maksimum tercapai.
Untuk kasus dengan multiple input (regresi linear berganda), model bekerja dengan cara yang sama namun menggunakan persamaan yang lebih kompleks: y = b0 + b1x1 + b2x2 + ... + bnxn, di mana setiap x mewakili variabel input yang berbeda dan setiap b adalah koefisien yang perlu dipelajari model. Model tetap menggunakan prinsip yang sama untuk menemukan kombinasi koefisien optimal yang menghasilkan prediksi paling akurat.
Keakuratan model regresi linear dapat dievaluasi menggunakan berbagai metrik seperti Mean Squared Error (MSE), Root Mean Squared Error (RMSE), atau R-squared (R²). MSE mengukur rata-rata kuadrat error antara prediksi dan nilai aktual, RMSE adalah akar kuadrat dari MSE yang memberikan nilai error dalam unit yang sama dengan variabel target, sedangkan R² mengukur seberapa baik model menjelaskan variasi dalam data target, dengan nilai berkisar antara 0 hingga 1.
Meskipun sederhana, regresi linear menjadi fondasi penting dalam machine learning dan sering digunakan sebagai baseline model atau untuk kasus-kasus di mana hubungan antara variabel bersifat linear. Model ini juga memiliki keunggulan dalam hal interpretabilitas, di mana koefisien model dapat langsung diinterpretasikan sebagai besarnya pengaruh masing-masing variabel input terhadap output.
"""

# ======================
# Hybrid TF-IDF Summary
# ======================
def hybrid_summary(text):
    stopw = set(stopwords.words("indonesian"))

    # Tokenize sentences (acts like Java's tb_tweet corpus)
    sentences = sent_tokenize(text)
    total_sentences = len(sentences)

    # Tokenize words per sentence
    sentence_tokens = []
    for sentence in sentences:
        tokens = [
            w.lower() for w in word_tokenize(sentence)
            if w.isalpha() and w.lower() not in stopw
        ]
        sentence_tokens.append(tokens)

    # ======================
    # Document Frequency (DF)
    # ======================
    df = defaultdict(int)
    for tokens in sentence_tokens:
        for word in set(tokens):
            df[word] += 1

    # ======================
    # Inverse Document Frequency (IDF)
    # log2(total_sentences / df)
    # ======================
    idf = {}
    for word, freq in df.items():
        idf[word] = math.log2(total_sentences / freq)

    # ======================
    # Sentence TF-IDF Score
    # ======================
    sentence_scores = {}

    for sentence, tokens in zip(sentences, sentence_tokens):
        tf = defaultdict(int)
        for word in tokens:
            tf[word] += 1

        total_words = len(tokens)
        score = 0.0

        for word in tf:
            tf_value = tf[word] / total_words
            score += tf_value * idf[word]

        sentence_scores[sentence] = score

    # ======================
    # Threshold (Java-style)
    # ======================
    average_score = sum(sentence_scores.values()) / len(sentence_scores)

    summary = " ".join([
        sentence for sentence in sentences
        if sentence_scores[sentence] >= average_score
    ])

    # ======================
    # Debug Output
    # ======================
    print("IDF:")
    for k, v in idf.items():
        print(f"{k}: {v:.4f}")

    print("\nSentence Scores:")
    for k, v in sentence_scores.items():
        print(f"{v:.4f} -> {k}")

    print("\nSummary:")
    print(summary)


# ======================
# Run
# ======================
hybrid_summary(text)

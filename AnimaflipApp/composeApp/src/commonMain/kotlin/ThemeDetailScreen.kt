import Model.ConnectedUser
import Model.Theme
import Service.ApiConfig
import Service.ApiService
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material.Button
import androidx.compose.material.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.*
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import chaintech.videoplayer.model.PlayerConfig
import chaintech.videoplayer.ui.video.VideoPlayerView
import androidx.compose.runtime.*
import androidx.compose.ui.graphics.Color
import kotlinx.coroutines.launch


@Composable
fun ThemeDetailScreen(theme: Theme, connectedUser: ConnectedUser, onBack: () -> Unit) {
    val apiService = remember { ApiService() }
    var showPlayer by remember { mutableStateOf(false) }
    var videoError by remember { mutableStateOf(false) }
    var currentAnimationIndex by remember { mutableStateOf(0) }
    val currentAnimation = theme.animations[currentAnimationIndex]
    val currentAnimationUrl ="${currentAnimation.video_url}/${connectedUser.token}"

    val coroutineScope = rememberCoroutineScope()

    // Utiliser LaunchedEffect pour tester l'URL de la vidéo
    LaunchedEffect(currentAnimation.video_url) {
        coroutineScope.launch {
            val url = "${ApiConfig.BASE_URL}${currentAnimationUrl}"
            videoError = !apiService.isVideoUrlAccessible(url)
            showPlayer = !videoError
        }
    }

    Column(
        modifier = Modifier.fillMaxSize().padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(text = theme.libelle, style = TextStyle(fontSize = 20.sp))

        Spacer(modifier = Modifier.height(16.dp))

        Text(text = currentAnimation.libelle, style = TextStyle(fontSize = 28.sp))

        Spacer(modifier = Modifier.height(16.dp))

        if (currentAnimation.video_url.isNotEmpty() && showPlayer) {
            VideoPlayerView(
                modifier = Modifier.fillMaxWidth().height(300.dp),
                url = "${ApiConfig.BASE_URL}${currentAnimationUrl}",
                playerConfig = PlayerConfig(
                    isPauseResumeEnabled = true,
                    isSeekBarVisible = false,
                    isDurationVisible = false,
                    isMuteControlEnabled = true,
                    isSpeedControlEnabled = false,
                    isFullScreenEnabled = false,
                    isScreenLockEnabled = false,
                    isAutoHideControlEnabled = true,
                    controlHideIntervalSeconds = 1,
                    isFastForwardBackwardEnabled = true,
                    pauseResumeIconSize = 100.dp
                )
            )
        } else if (videoError) {
            Text(
                text = "Erreur : Impossible de lire la vidéo.",
                color = Color.Red,
                style = TextStyle(fontSize = 20.sp)
            )
        } else {
            Text(
                text = "Loading...",
                color = Color.Blue,
                style = TextStyle(fontSize = 20.sp)
            )
        }

        Spacer(modifier = Modifier.height(5.dp))

        Row(modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically) {

            if (currentAnimationIndex > 0) {
                Button(onClick = {
                    if (currentAnimationIndex > 0) currentAnimationIndex--
                },
                    modifier = Modifier
                        .weight(1f) // Remplir l'espace disponible
                        .height(80.dp) // Augmenter la hauteur du bouton
                        .padding(start = 8.dp) ) {
                    Text("Précédent", fontSize = 18.sp)
                }
            } else {
                Spacer(modifier = Modifier.weight(1f)) // Espace réservé si le bouton n'est pas affiché
            }

            if (currentAnimationIndex < (theme.animations.size)-1) {
                Button(onClick = {
                    if (currentAnimationIndex < theme.animations.size - 1) currentAnimationIndex++
                },
                    modifier = Modifier
                        .weight(1f) // Remplir l'espace disponible
                        .height(80.dp) // Augmenter la hauteur du bouton
                        .padding(start = 8.dp)) {
                    Text("Suivante", fontSize = 18.sp)
                }
            } else {
                Spacer(modifier = Modifier.weight(1f)) // Espace réservé si le bouton n'est pas affiché
            }
        }

        Spacer(modifier = Modifier.height(5.dp))

        Button(onClick = onBack,
            modifier = Modifier
                .fillMaxWidth()
                .height(70.dp) // Augmenter la hauteur du bouton
                .padding(top = 16.dp)) {
            Text("Retour à la liste des thèmes")
        }
    }
}